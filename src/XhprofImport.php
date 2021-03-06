<?php

class XhprofImport {
    public $baseDir = '';
    protected $_file;
    protected $_client;
    protected $_raw;
    protected static $_nodes = array();

    const FDELIM = '.';

    /**
     * Imports all *.xhprof files from the specified directory.
     *
     * @param string $dir
     */
    public static function importAll($dir) {
	$p = new NewParser($file);
	foreach (glob("{$dir}/*.xhprof") as $file) {
	    $p->load(basename($file));
	    $p->makeGraph();
	}
    }

    /**
     * Saves the run data directly from the output of xhprof_disable()
     */
    public static function saveRun($data, $filename) {
        $import = new XhprofImport();
        file_put_contents("{$import->baseDir}/{$filename}.xhprof");
        $import = new XhprofImport("{$filename}.xhprof");
        $import->makeGraph();
    }

    /**
     * Constructor.
     * 
     * @param string $file The xhprof file to import.
     */
    public function __construct($file='') {
        $this->baseDir = Config::$xhdata;
        $this->_client = get_client();
        $file = basename($file);
        $this->load($file);
    }

    /**
     * Gets the raw data from the file.
     * Called by the constructor, but you can use this to import in a loop
     * 
     * @see XhprofImport::importAll
     * @param string $file The file to load.
     */
    public function load($file) {
	if (file_exists($this->baseDir . $file)) {
	    $this->_file = "{$this->baseDir}{$file}";
	    $this->raw(unserialize(file_get_contents($this->_file)));
	    list($this->runId, $this->script) = $this->parseFilename($file);
	} else {
	    die("File {$this->baseDir}{$file} doesn't exist");
	}
    }

    /**
     * Splits the filename into parts.
     * This needs abstracted to be configurable, maybe.
     *
     * @param string $filename The filename to parse. 
     * @return array 
     */
    public function parseFilename($filename) {
	$name = str_replace('.xhprof', '', basename($filename));
	$parts = explode(self::FDELIM, $name);
        if (sizeof($parts) === 3) {
            return array($parts[0], $parts[2]);
        } elseif (sizeof($parts) == 2) {
            return array($parts[0], $parts[1]);
        }
        return array($parts[0], 'unset');
    }


    /**
     * Searches the DB for an existing match and returns it, or creates a new one.
     * @param string $method The method name.
     * @param bool $create (default True) If false, returns False if node does not exist.
     * @return mixed node object or false
     */
    public function getNode($method, $create=true) {
	if (!isset(self::$_nodes[$method]) || empty(self::$_nodes[$method])) {
	    $name = $method;
	    $class = '';
	    if (strpos($method, '::')) {
		list ($class, $name) = explode('::', $method);
	    }

            // Removed short-circuit for `run_init::*` and `load::*`, though semantically, 
            // they shouldn't use the "Callable" label. 

	    // Check to see if it exists
            // Consider ($create) ? 'CREATE UNIQUE' : 'MATCH'...
	    $query = new Everyman\Neo4j\Cypher\Query($this->_client, 
						     'MATCH n 
                                                      WHERE (HAS(n.name) AND HAS(n.class)) 
                                                          AND (n.name = {name} AND n.class = {class}) 
                                                      RETURN n', 
						     array('name' => $name, 'class' => $class));
	    $result = $query->getResultSet();
	    $found = false;
	    if ($result) {
		foreach ($result as $row) {
		    if (($row['x']->getProperty('name') == $name) && ($row['x']->getProperty('class') == $class)) {
			self::$_nodes[$method] = $row['x'];
			$found = true;
			break;
		    }
		}
	    } 

	    // If not found, create it
	    if (!$found) {
                // maybe...
                if (!$create) {
                    return false; 
                }
		$node = $this->_client->makeNode()->setProperty('name', $name)->save();
		$node->setProperty('class', $class)->save();
		$node->addLabels(array($this->_client->makeLabel('Callable')));
		self::$_nodes[$method] = $node;
	    }
	}
	$x = &self::$_nodes[$method];
	return $x;
    }

    /**
     * Finds/creates the appropriate nodes and inserts to Neo4J
     */
    public function makeGraph() {
	if (!$this->_raw) {
	    return false;
	}

	$main = $this->getNode($this->runId.'::main()', false);
        if ($main === false) {
            $main = $this->getNode($this->runId.'::main()');
            $main->setProperty('runId', $this->runId)->save();
            $main->setProperty('scriptName', $this->script)->save();
            $main_stats = array();
            ksort($this->_raw);
            foreach ($this->_raw as $callable => $stats) {
                if ($callable == 'main()') continue;
                list($parent, $child) = explode('==>', $callable);
                if ($parent == 'main()') {
                    $pNode = $main;
                } else {
                    $pNode = $this->getNode($parent);
                }
                $cNode = $this->getNode($child);
                
                if ($pNode && $cNode) {
                    $this->addChildCall($pNode, $cNode, $stats);
                    
                    foreach ($stats as $k=>$v) {
                        if (!isset($main_stats[$k])) $main_stats[$k] = 0;
                        $main_stats[$k] += $v;
                    }
                }
            }
            // Write overall stats for main()
            foreach ($main_stats as $k => $v) {
                $main->setProperty($k, $v);
            }
            
            $main->save();
            return true;
        }
        return false; // Already exists
    }

    public function addChildCall(&$parent, &$child, $stats) {
        // This is super-slow.
        //$rels = $parent->getRelationships(array('called'));
	$found = false;
	$rel = $parent->relateTo($child, 'called');
	foreach ($stats as $k => $v) {
	    $rel->setProperty($k, $v);
	}
	$rel->setProperty('runId', $this->runId)->save();
    }

    public function raw($raw=null) {
        if (is_array($raw)) {
            $this->_raw = $raw;
        }
        return $this->_raw;
    }
}
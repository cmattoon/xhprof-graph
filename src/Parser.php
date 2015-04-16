<?php

class Parser {
    
    public $baseDir = '/tmp/xhdata/';
    protected $_file;
    protected $_client;
    protected $_raw;
    public static $fileparts = array('Run', 'Script');
    /**
     * @hacky
     * The character to split filename 'fields' on.
     */
    const FDELIM = '.';


    public function __construct($f) {
	$this->_client = new Everyman\Neo4j\Client('localhost', 7474);
	$this->_client->getTransport()->setAuth('neo4j', 'password');


	if (file_exists($this->baseDir . $f)) {
	    $this->_file = $this->baseDir.$f;
	    $this->_raw = unserialize(file_get_contents($this->_file));
	}
    }
    

    /**
     * Parses a filename and decodes namespaces/runTypes.
     * runA.fileB.2015-01-01.xhprof => (run, file, date)
     * Name parts from outer layer:
     * run = main()
     * run -> classes -> methods -> child 
     */
    public function getNodesFromFilename($filename='', $labels=null) {
	$filename = ($filename == '') ? $this->_file : $filename;
	// explode(self::FDELIM, $filename);

	if ($labels === null) {
	    // Default labels here:
	    $labels = self::$fileparts;
	}

	$data = explode(self::FDELIM, $filename);
	$nodes = array();
	// Each *.xhprof file represents one run, so we always have a run.
	// (Run == Root)
	$parent = $this->_makeNode('Run', 'Run', array(
	    'RunId' => basename($data[0])) // $data[0] is '/tmp/xhdata/adfjlf...'
	);
	
	$nodes[] = $parent;

	$len = count($data);
	/**
	 * Here, loop through the filename parts and if there's a defined
	 * label (self::$fileparts), assign that label.
	 * @example If self::$fileparts = array('run', 'date', 'set', 'file')
	 * @example if filename =     
	 *     'runId32490234.yyyy-mm-dd.PreMySQLIndexingBenchmark.login.xhprof'
	 * @example resulting node/relationships:
	 *     (n:run {runId: 'runId32490234'})-[:CALLS]->
	 *        (d:date {date: 'yyyy-mm-dd'})-[:CALLS]->
	 *        (s:set {set: 'PreMySQLIndexingBenchmark'})-[:CALLS]->
	 *        (f:file {file: 'login.php'})
	 */
	for ($i=1; $i < $len; $i++) {
	    $part = $data[$i];
	    $name = (isset($labels[$i])) ? $labels[$i] : null;
	    $node = null;

	    if ($name) {
		$node = $this->_makeNode($name, $name, array($name => $part));
		
		if ($node && isset($nodes[($i-1)])) {
		    // Make this a child of the last node
		    $p = $nodes[($i-1)];
		    if ($p) {
			$this->attach($p, $node, 'CALLS');
			$p->save();
		    }
		    $nodes[] = $node;
		}
	    }
	    $node = $name = $p = null;
	}
	return $nodes;
	
    }
    /**
     * I'm not really sure what I'm doing here. I think there's either:
     *    1) Some recursion or a loop
     *    2) Way more data than I anticipated
     *    3) A horribly flawed design
     *    4) A fundamental misunderstanding of the whole graph DB thing.
     * I ran this inside a foreach(glob(xhprof_dir)) and it seemed to take 
     * forever. I cancelled it. The following was the result of
     * MATCH(n)RETURN(n):
     *     *(861) Class(39) Function(290) Method(530) Run(1) Script(1)
     *     *(764) CALLS(388) HAS_METHOD(376)
     *
     * It's not too much data, but I wonder if the data is structured
     * correctly or not.
     */
    public function makeGraph() {
	$parents = $this->getNodesFromFilename();
	$root = $parents[0];
	$this->run = end($parents); // This is 'main()'
	
	$lblFunction = $this->_getLabel('Function');
	$lblClass = $this->_getLabel('Class');
	$lblMethod = $this->_getLabel('Method');
	$lblNative = $this->_getLabel('NativeFunction');

	foreach ($this->_raw as $caller => $stats) {
	    $objPC = $objPM = $objCC = $objCM = null;
	    $parsed = $this->_parseCaller($caller);
	    list($pClass, $pMethod, $cClass, $cMethod) = $parsed;
	    // Splits the strings on '==>', then each half again on '::'
	    // [null] main()==>process_login_form()
	    // [null] process_login_form()==>User::login()
	    // User::login()==>crypto::hashPassword()
	    // User::login()==>mysql::query()
	    
	    echo "[{$pClass}]==>[{$pMethod}]==>[{$cClass}]==>[{$cMethod}]\n";
	    
	    if ($cMethod == 'main()') {
		// Actually, I think we want main()'s inclusive stats.
		// Currently, they're not being gathered
		continue;
	    }
	    // There should ALWAYS be a child method.
	    if (!$cMethod) continue; 

	    // If the child has a class: add it to it's class,
	    // then pass the class object as the new chaining point.
	    // (cmethod)
	    // (cclass)-[HAS_METHOD]->(cmethod)

	    if ($cClass) {
		$tmpCM = $this->_makeNode($cMethod, 'Method', $stats);
		$tmpCC = $this->_makeNode($cClass, 'Class');
		$this->attach($tmpCC, $tmpCM, 'HAS_METHOD');
		$objCM = $tmpCC;
	    } else {
		// If there's no child class, this is probably a function
		$objCM = $this->_makeNode($cMethod, 'Function', $stats);
	    }
	    
	    if ($pMethod == 'main()') {
		// If the parent method is 'main()', attach this 
		// call to the run, since it's happening in the
		// main routine
		$this->attach($this->run, $objCM, 'CALLS');
	    } else {
		if ($pClass) {
		    // If the parent belongs to a class, add it here.
		    // Attach to child method (or $tmpCC disguised as $objCM)
		    $objPM = $this->_makeNode($pMethod, 'Method');
		    $this->attach($objPM, $objCM, 'CALLS');
		} else {
		    $objPM = $this->_makeNode($pMethod, 'Function');
		}
	    }
	    
	    if ($pClass) {
		$objPC = $this->_makeNode($pClass, 'Class');
		$this->attach($objPC, $objPM, 'HAS_METHOD');
		$objPM = $objPC;
	    }

	    if ($pMethod != 'main()') {
		$this->attach($this->run, $objPM, 'CALLS');
	    }
	}
    }
    /**
     * Makes $child a child of $parent.
     * Looks for an existing node with the same relationship name
     */
    public function attach(&$parent, &$child, $rel_name) {
	$parent->save();
	$child->save();

	$rels = $parent->getRelationships($rel_name);
	$insert = true;
	foreach ($rels as $rel) {
	    $node = $rel->getEndNode();
	    if ($node->getProperty('name') == $child->getProperty('name')) {
		$insert = false;
	    }
	}
	if ($insert) {
	    $parent->relateTo($child, $rel_name)->save();
	}
    }

    /**
     * Converts string $name into label object
     * @param string $name The label name
       @example For (n:ItemLabel), $name = 'ItemLabel'
     * @return Label 
     */
    public function _getLabel($name) {
	$lbl = $this->_client->makeLabel($name);
	return $lbl;
    }
    
    /**
     * This needs some work.
     * I'm not sure exactly where to put the data, or how to structure it.
     * Right now:
     *  (runId)-[calls]-(n:Script)-[calls]-(n:ParentClass)-[HAS_METHOD]-
     *      (m:ParentMethod)-[calls]->(o:ChildClass)-[HAS_METHOD]->
     *      (p:ChildMethod)
     *
     * Need to figure that out, plus when to consider nodes unique/non-unique
     */
    public function _makeNode($name, $label, $stats=null) {
	//http://www.markhneedham.com/blog/2013/10/22/neo4j-modelling-hyper-edges-in-a-property-graph/
	$query = "START n=node(*) 
                WHERE n:{$label} AND HAS(n.name) 
                    AND n.name = {name} ";
	$bindings = array('name' => $name);
	if (is_array($stats) && count($stats)) {
	    foreach ($stats as $k => $v) {
		$query .= "AND (n.{$k} = {{$k}}) ";
		$bindings[$k] = $v;
	    }
	}
	$query .= "RETURN n";
	$query = new Everyman\Neo4j\Cypher\Query($this->_client, 
						 $query,
						 $bindings
						 );
	$result = $query->getResultSet();
	foreach ($result as $row) {
	    if ($row['n']) {
		return $row['n'];
	    }
	}
	// else... create it
	$node = $this->_client->makeNode()->setProperty('name', $name)->save();

	if (is_array($stats)) {
	    foreach ($stats as $key => $value) {
		$node->setProperty($key, $value);
	    }
	}
	
	$label = $this->_getLabel($label);
	$node->addLabels(array($label));
	$node->save();
	return $node;
    }

   
    /**
     * The array keys of the xhprof raw array indicate a portion of the 
     * call stack for that function. This pareses that string and returns
     * an array of four elements (default null) containing the string names.
     * @example
     *      $caller_str = User::login()==>crypto::checkPassword()
     * would return:
     * array('User', 'login()', 'crypto', 'checkPassword()');
     * @todo - Handle run_init::path/to/file
     * @todo - Handle load::path/to/file
     */
    public function _parseCaller($caller_str) {
	$caller = explode('==>', $caller_str);
	$pClass = $pMethod = $cClass = $cMethod = $parent = $child = null;

	$child = $caller[0];
	if (count($caller) == 2) {
	    $parent = $caller[0];
	    $child = $caller[1];
	}
	$pMethod = $parent;
	$cMethod = $child;

	/* Parse class methods */
	if ($parent && strpos($parent, '::') !== false) {
	    list($pClass, $pMethod) = explode('::', $parent);
	}
	if (strpos($child, '::') !== false) {
	    list($cClass, $cMethod) = explode('::', $child);
	}
	return array(
	    $pClass,
	    $pMethod,
	    $cClass,
	    $cMethod
	);
    }
}
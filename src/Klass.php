<?php
/** 
 * This class represents classes from the profiled script.
 * Because spaghetti code.
 */
class Klass {
    public $className = '';
    protected $_client = null;
    protected static $cache = array();
    
    /**
     * Constructor.
     * @param string $className
     */
    public function __construct($className) {
	$this->className = trim($className);
	$this->_client = get_client();
    }
    /**
     * Returns info about the class, namely the methods we know about
     * and their stats. This really should create Method objects.
     *
     * @param bool $force Force getting from DB (not cache)
     * @param string $sort The sort-by clause
     */
    public function getClassStats($force=false, $sort=null) {
	if ($sort === null) {
	    $sort = array('AVG(r.wt)' => 'desc', 'AVG(r.cpu)' => 'desc');
	}
	$sSort = array();
	foreach ($sort as $item => $dir) {
	    $sSort[] = "{$item} {$dir}";
	}
	$sSort = trim(implode(',', $sSort));
	
	$cached = (array_key_exists($this->className, self::$cache) &&
	    array_key_exists($sSort, self::$cache[$this->className]));

	if ($force || !$cached) {
	    $qstr = "
                MATCH (n:Callable)<-[r:called]-(m)
                WHERE (HAS(n.name) AND HAS(n.class) AND n.class = {class})
                RETURN n.class, n.name, AVG(r.wt), AVG(r.ct), AVG(r.cpu), 
                    AVG(r.mu), AVG(r.pmu)
                ORDER BY {$sSort}
            ";
	    $query = new Everyman\Neo4j\Cypher\Query($this->_client, $qstr,
						     array('class' => $this->className));
	    $stats = array();
	    foreach (($result = $query->getResultSet()) as $row) {
		$stats[] = array(
		    'class' => $row['n.class'],
		    'name' => $row['n.name'],
		    'ct' => $row['AVG(r.ct)'],
		    'wt' => $row['AVG(r.wt)'],
		    'cpu' => $row['AVG(r.cpu)'],
		    'mu' => $row['AVG(r.mu)'],
		    'pmu' => $row['AVG(r.pmu)']
		);
	    }
	    if (!array_key_exists($this->className, self::$cache)) {
		self::$cache[$this->className] = array();
	    }
	    self::$cache[$this->className][$sSort] = $stats;
	}
	return self::$cache[$this->className][$sSort];
    }

    public function getJsonForField($field='wt') {
	$fields = array('wt', 'cpu', 'mu', 'pmu'); // Global config.
	$field = (in_array($field, $fields)) ? $field : 'wt';
	$stats = $this->getClassStats(false, array(
	    "AVG(r.{$field})" => 'desc'));
	$len = count($stats);
	$data = array();
	$nc = count(Colors::$colors); // belongs in Colors
	for ($i=0;$i<$len;$i++) {
	    if ($i > 10 || !isset($stats[$i])) break;
	    $name = ($stats[$i]['class'] == '') ? $stats[$i]['name'] : 
		    "{$stats[$i]['class']}::{$stats[$i]['name']}";
	    $value = (int)$stats[$i][$field];
	    if (!$value) {
		$value = 1;
	    }
	    $data[] = array(
		'value' => $value,
		'color' => Colors::$colors[$i%$nc],
		'label' => $name
	    );
	}
	return json_encode($data);
    }
}

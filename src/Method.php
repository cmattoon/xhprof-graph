<?php

class Method {
    public $className = '';
    public $methodName = '';

    protected $_client = null;

    /**
     * Constructor.
     * @param string $method_name The method name, including class name
     * @example `$method = new Method('Method::__construct')
     */
    public function __construct($method_name) {
	$parts = explode('::', $method_name);

	if (count($parts) == 1) {
	    $this->methodName = $parts[0];
	} elseif (count($parts) == 2) {
	    list($this->className, $this->methodName) = $parts;
	} 

	$this->_client = get_client();
    }
    
    /**
     * Returns data about the method.
     * @todo - cache everything, filter cache by runId
     *
     * @param string $runId (optional, default '') If present, returns run-specific data. Otherwise,
     *     return all the data.
     * @return array
     */
    public function getData($runId='') {
	$qstr = "MATCH (n:Callable)<-[r:called]-(m)
	WHERE (HAS(n.name) AND HAS(n.class) AND (n.class = {class} AND n.name = {name}))
	    RETURN n.class, n.name, r.wt, r.ct, r.cpu, r.mu, r.pmu, r.runId, m.name, m.class
	ORDER BY r.wt DESC, r.cpu DESC";

	$vars = array('class' => $this->className, 'name' => $this->methodName);
	$query = new Everyman\Neo4j\Cypher\Query($this->_client, $qstr, $vars);
	$result = array();

	foreach (($r = $query->getResultSet()) as $row) {
	    if ($runId == '' || ($runId != '' && $row['r.runId'] == $runId)) {
		$result[] = array(
		    'pclass' => $row['m.class'],
		    'pname' => $row['m.name'],
		    'class' => $row['n.class'],
		    'name' => $row['n.name'],
		    'run' => $row['r.runId'],
                    'ct' => $row['r.ct'],
		    'wt' => $row['r.wt'],
		    'cpu' => $row['r.cpu'],
		    'mu' => $row['r.mu'],
		    'pmu' => $row['r.pmu']
		);
	    }
	}
	return $result;
    }

    /**
     * Returns an array of child methods called by this method.
     * If 'run' is specified, returns specifics for that run. Otherwise, averages are returned.
     *
     * @param string $run
     * @return array
     */
    public function getChildren($runId='') {
        $qstr = "MATCH (n:Callable)<-[r:called]-(m:Callable)
	WHERE (HAS(m.name) AND HAS(m.class) AND (m.class = {class} AND m.name = {name}))
	    RETURN m.class, m.name, r.wt, r.ct, r.cpu, r.mu, r.pmu, r.runId, n.name, n.class
	ORDER BY r.wt DESC, r.cpu DESC";

	$vars = array('class' => $this->className, 'name' => $this->methodName);
	$query = new Everyman\Neo4j\Cypher\Query($this->_client, $qstr, $vars);
	$result = array();

	foreach (($r = $query->getResultSet()) as $row) {
	    if ($runId == '' || ($runId != '' && $row['r.runId'] == $runId)) {
		$result[] = array(
		    'pclass' => $row['m.class'],
		    'pname' => $row['m.name'],
		    'class' => $row['n.class'],
		    'name' => $row['n.name'],
		    'run' => $row['r.runId'],
                    'ct' => $row['r.ct'],
		    'wt' => $row['r.wt'],
		    'cpu' => $row['r.cpu'],
		    'mu' => $row['r.mu'],
		    'pmu' => $row['r.pmu']
		);
	    }
	}
        return $result;
    }
}
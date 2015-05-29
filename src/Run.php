<?php

class Run {
    public $runId = '';
    protected $_client = null;
    protected static $statsCache = array();
    /**
     * Constructor.
     *
     * @param string $runId
     */
    public function __construct($runId) {
        $this->runId = trim($runId);
        $this->_client = get_client();
    }

    /**
     * Returns run stats, obviously.
     * Should handle via Neo4J objects...
     */
    public function getRunStats($force=false, $sort=null) {
	if ($sort === null) {
	    $sort = array('r.cpu' => 'desc', 'r.wt' => 'desc');
	}
	$sSort = array();
	foreach ($sort as $item => $dir) {
	    $sSort[] = "{$item} {$dir}";
	}
	$sSort = trim(implode(',', $sSort));

	$cached = (array_key_exists($this->runId, Run::$statsCache) &&
	    array_key_exists($sSort, Run::$statsCache[$this->runId]));

	if ($force || !$cached) {
            $query = new Everyman\Neo4j\Cypher\Query($this->_client,
                                                     "MATCH (n:Callable)<-[r:called]-(m)
WHERE ((HAS(r.runId) AND r.runId = {runId})
AND (HAS(m.name) AND m.name = 'main()'))
RETURN n.name, n.class, r.runId, r.wt, r.cpu, r.mu, r.pmu, r.ct, n.scriptName
ORDER BY {$sSort} ",
                                                     array('runId' => $this->runId));
            $stats = array();
            foreach (($result = $query->getResultSet()) as $row) {
		$stats[] = array(
                    'runId' => $row['r.runId'],
                    'name' => $row['n.name'],
                    'class' => $row['n.class'],
                    'ct' => $row['r.ct'],                           
                    'wt' => $row['r.wt'],
                    'cpu' => $row['r.cpu'],
                    'mu' => $row['r.mu'],
                    'pmu' => $row['r.pmu']
		);
            }
	    if (!array_key_exists($this->runId, Run::$statsCache)) {
		Run::$statsCache[$this->runId] = array();
	    }
	    Run::$statsCache[$this->runId][$sSort] = $stats;
	}
        return Run::$statsCache[$this->runId][$sSort];
    }
    /**
     * Outputs JSON for Pie Chart
     */
    public function getJSONForPieChart($field='wt') {
	$fields = array('wt','cpu','mu','pmu');
	$field = (in_array($field, $fields)) ? $field : 'wt';
	$stats = $this->getRunStats(false, array(
	    "r.{$field}" => 'desc'
	));
	$len = count($stats);
	$data = array();
	$nc = count(Colors::$colors);

	for ($i=0;$i<$len;$i++) {
	    if ($i > 10 || !isset($stats[$i])) break;

	    $name = ($stats[$i]['class'] == '') ? $stats[$i]['name'] : 
		    "{$stats[$i]['class']}:{$stats[$i]['name']}";
	    $value = (int)$stats[$i][$field];

	    if (!$value) {
		$value = 1; // All pie chart segments > 0
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
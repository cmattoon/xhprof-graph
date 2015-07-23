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
	    $sort = array('inc_cpu' => 'desc', 'inc_wt' => 'desc');
	}
	$sSort = array();
	foreach ($sort as $item => $dir) {
	    $sSort[] = "{$item} {$dir}";
	}
	$sSort = trim(implode(',', $sSort));

	$cached = (array_key_exists($this->runId, Run::$statsCache) &&
	    array_key_exists($sSort, Run::$statsCache[$this->runId]));

	if ($force || !$cached) {
            $vars = array('runId' => $this->runId);
            $query = new Everyman\Neo4j\Cypher\Query($this->_client, "
                MATCH (c:Callable)<-[x:called]-(n:Callable)<-[r:called]-(m)
                WHERE ((HAS(r.runId) AND r.runId = {runId})
                    AND (HAS(m.name) AND m.name = 'main()'))
                RETURN n.class, n.name, 
                    r.ct,
                    r.wt AS inc_wt, 
                    r.cpu AS inc_cpu, 
                    r.mu AS inc_mu, 
                    r.pmu AS inc_pmu, 
                    (r.wt-SUM(x.wt)) AS exc_wt, 
                    (r.cpu-SUM(x.cpu)) AS exc_cpu, 
                    (r.mu-SUM(x.mu)) AS exc_mu, 
                    (r.pmu - SUM(x.pmu)) AS exc_pmu
               ORDER BY {$sSort}", $vars);

            $stats = array();
            foreach (($result = $query->getResultSet()) as $row) {
		$stats[] = array(
                    'runId' => $row['r.runId'],
                    'name' => $row['n.name'],
                    'class' => $row['n.class'],
                    'ct' => $row['r.ct'],
                    'exc_wt' => $row['exc_wt'],
                    'exc_cpu' => $row['exc_cpu'],
                    'exc_mu' => $row['exc_mu'],
                    'exc_pmu' => $row['exc_pmu'],
                    'inc_wt' => $row['inc_wt'],
                    'inc_cpu' => $row['inc_cpu'],
                    'inc_mu' => $row['inc_mu'],
                    'inc_pmu' => $row['inc_pmu'],
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
        $field = "exc_{$field}"; // Always display exclusive stats for graphs
	$stats = $this->getRunStats(false, array(
	    "{$field}" => 'desc'
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
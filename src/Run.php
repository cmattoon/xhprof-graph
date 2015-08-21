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
                WHERE (HAS(m.name) AND m.name = 'main()')
                    AND (
                        HAS(r.runId) AND r.runId = {runId} 
                        AND HAS(x.runId) AND x.runId = {runId}
                    )
                RETURN n.class, n.name, 
                    r.ct,
                    r.wt AS inc_wt, 
                        (r.wt - REDUCE(s=0, w in COLLECT(x.wt)|s+w)) AS exc_wt,
                    r.cpu AS inc_cpu, 
                        (r.cpu - REDUCE(s=0, w in COLLECT(x.cpu)|s+w)) AS exc_cpu,
                    r.mu AS inc_mu, 
                        (r.mu - REDUCE(s=0, w in COLLECT(x.mu)|s+w)) AS exc_mu,
                    r.pmu AS inc_pmu, 
                        (r.pmu - REDUCE(s=0, w in COLLECT(x.pmu)|s+w)) AS exc_pmu
               ORDER BY {$sSort}", $vars);
            /**
             * The above reductions appear to be correct in the Neo4J console, 
             * but don't match XHProf's native interface because of excluding
             * the run_init:: and load:: calls.
             * This should match up better once those are factored in.
             */
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
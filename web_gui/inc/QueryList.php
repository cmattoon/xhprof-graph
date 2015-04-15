<?php
/**
 * Being a n00b and storing everything here for now.
 */
class QueryList {
    protected $_client = null;

    public function __construct() {
	$this->_client = get_client();
    }
    /**
     * @param string $query The Cypher query string
     * @param array $vars Vars to bind
     * @return Everyman\Neo4j\Cypher\Query
     */
    public function getQuery($query, $vars=array()) {
	$oQuery = new Everyman\Neo4j\Cypher\Query($this->_client, $query, $vars);
	return $oQuery;
    }
    
    /**
     * Gets all the stats from a given run.
     * @param string $runId
     * @return array
     */
    public function getRunStats($runId) {
	$query = $this->getQuery("
	    MATCH (n:Callable)<-[r:called]-(m)
		WHERE ((HAS(r.runId) AND r.runId = {runId}) AND (HAS(m.name) AND m.name = 'main()'))
		    RETURN n.name, n.class, r.runId, r.wt, r.cpu, r.mu, r.pmu, r.ct
	    ORDER BY r.cpu DESC, r.wt DESC", array('runId' => $runId));
	$stats = array();
	foreach (($result = $query->getResultSet()) as $row) {
	    $stats[] = array(
		'runId' => $row['n.runId'],
		'name' => $row['n.name'],
		'class' => $row['n.class'],
		'ct' => $row['r.ct'],
		'wt' => $row['r.wt'],
		'cpu' => $row['r.cpu'],
		'mu' => $row['r.mu'],
		'pmu' => $row['r.pmu']
	    );
	}
	return $stats;
    }
    
    public function resetDatabase() {
	$query = $this->getQuery("START n=node(*) MATCH n-[r]-() DELETE r");
	$query->getResultSet();
	$query = $this->getQuery("START n=node(*) MATCH n DELETE n");
	$query->getResultSet();
    }
}
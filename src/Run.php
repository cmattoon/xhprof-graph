<?php

class Run {
    public $runId = '';
    protected $_client = null;
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
    public function getRunStats() {
        $query = new Everyman\Neo4j\Cypher\Query($this->_client,
                                                 "MATCH (n:Callable)<-[r:called]-(m)
WHERE ((HAS(r.runId) AND r.runId = {runId})
AND (HAS(m.name) AND m.name = 'main()'))
RETURN n.name, n.class, r.runId, r.wt, r.cpu, r.mu, r.pmu, r.ct, n.scriptName
ORDER BY r.cpu desc, r.wt desc ",
                                                 array('runId' => $this->runId));
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
}
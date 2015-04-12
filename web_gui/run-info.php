<?php
require_once('inc/config.php');
$client = get_client();
$run = _get('run');
$runs = array();

if ($run) {
    $query = "MATCH (n:Callable)<-[r:called]-(m) 
WHERE (HAS(r.runId) AND HAS(m.name) AND m.name = 'main()' AND r.runId = {run})
RETURN n.name, n.class, r.runId, r.wt, r.cpu, r.mu, r.pmu, r.ct
ORDER BY r.cpu DESC, r.wt DESC";

    $q = new Everyman\Neo4j\Cypher\Query($client, $query, array('run'=>$run));
    $res = $q->getResultSet();

    foreach ($res as $r) {
	if ($r['n.runId']) {
	    $runs[] = array(
		'runId' => $r['n.runId'],
		'name' => $r['n.name'],
		'class' => $r['n.class'],
		'ct' => $r['r.ct'],
		'wt' => $r['r.wt'],
		'cpu' => $r['r.cpu'],
		'mu' => $r['r.mu'],
		'pmu' => $r['r.pmu']
	    );
	}
    }
}
$tpl = new Template();
$tpl->displayPage(array('runs' => $runs, 'runId' => $run));
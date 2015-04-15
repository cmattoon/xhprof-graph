<?php
require_once('inc/config.php');
$client = get_client();
$run = _get('run');
$runs = array();

if ($run) {
    $query = "MATCH (n:Callable)
WHERE (HAS(n.scriptName) AND HAS(n.runId) AND n.scriptName = {name})
RETURN n.runId";

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
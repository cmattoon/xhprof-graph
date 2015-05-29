<?php
require_once('inc/config.php');
$client = get_client();
$script = _get('script');

$runs = array();

if ($script) {
    $query = "MATCH (n:Callable)
WHERE (HAS(n.scriptName) AND HAS(n.runId) AND n.scriptName = {scriptName})
RETURN n.runId, n.scriptName";

    $q = new Everyman\Neo4j\Cypher\Query($client, $query, array('scriptName'=>$script));
    $res = $q->getResultSet();

    foreach ($res as $r) {
	if ($r['n.runId']) {
	    $runs[] = array(
		'runId' => $r['n.runId'],
		'name' => $r['n.scriptName'],
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
$tpl->displayPage(array('runs' => $runs, 'runData' => json_encode($runs)));
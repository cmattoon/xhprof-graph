<?php
require_once('inc/config.php');
$client = get_client();
$query = "MATCH (n:Callable) WHERE (HAS(n.runId) AND HAS(n.name) AND n.name = 'main()') RETURN n.runId, n.scriptName";

$q = new Everyman\Neo4j\Cypher\Query($client, $query);
$res = $q->getResultSet();

$runs = array();
foreach ($res as $r) {
    if ($r['n.runId']) {
	$runs[$r['n.runId']] = array(
	    'runId' => $r['n.runId'],
	    'scriptName' => $r['n.scriptName']
	);
    }
}

$tpl = new Template();
$tpl->displayPage(array('runs' => $runs));
<?php
require_once('inc/config.php');
$client = get_client();

/**
 * Handle *.xhprof file upload
 */
if (isset($_FILES['xhprof_file'])) {
    $file = $_FILES['xhprof_file'];
    if ($file['error'] === 0) {
	$newfile = "/tmp/xhdata/{$file['name']}";
	if (move_uploaded_file($file['tmp_name'], $newfile)) {
	    $parser = new NewParser($file['name']);
	    $parser->makeGraph();
	    $msgs[] = "Imported {$newfile}";	    
	} else {
	    $msgs[] = "Can't import {$file['tmp_name']}";
	}
    }
}
/**
 * Get run data from DB
 */
$q = new Everyman\Neo4j\Cypher\Query($client, 
				     "MATCH (n:Callable) 
WHERE (HAS(n.runId) AND HAS(n.name) AND n.name = 'main()') 
RETURN n.runId, n.scriptName");

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
$tpl->displayPage(array('runs' => $runs, 'msgs'=>$msgs));
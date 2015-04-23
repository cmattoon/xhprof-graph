<?php
require_once('inc/config.php');
$client = get_client();
$errors = array();
$msgs = array();

/**
 * Handle *.xhprof file upload
 */
if (isset($_FILES['xhprof_file'])) {
    try {
	$upload = new XhprofUpload($_FILES['xhprof_file']);
	$newfile = $upload->save();
	$import = new XhprofImport($newfile);
	$import->makeGraph();
	$msgs[] = "File ".basename($newfile)." imported successfully";
	
    } catch (Exception $e) {
	$errors[] = $e->getMessage();
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
$tpl->displayPage(array(
    'runs' => $runs, 
    'msgs'=>$msgs, 
    'errors' => $errors
));
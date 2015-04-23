<?php
require_once('../src/config.php');

$list = new FilesList(xhprof_dir);
/**
 * Check the DB to see if we have data for these files
 */
$runIds = array();
$file_list = array();
foreach (($files = $list->getFiles()) as $file) {
    $fname = str_replace('.xhprof', '', basename($file));
    $parts = explode('.', $fname);
    $runIds[] = $parts[0];
    $file_list[$parts[0]] = array('filename' => $fname, 'in_database' => false);
}

if (count($runIds)) {
    $client = get_client();
    $query = new Everyman\Neo4j\Cypher\Query($client, "MATCH (n:Callable)
WHERE (HAS(n.name) AND (n.name = 'main()') AND (HAS(n.runId) AND n.runId IN [{ids}]))
RETURN n.runId, n.scriptName
", array('ids' => implode(',', $runIds)));
    foreach ($query->getResultSet() as $row) {
	if ($row['n.runId']) {
	    $file_list[$row['n.runId']]['in_database'] = true;
	}
    }
}
$tpl = new Template();
$tpl->displayPage(array(
    'files' => $file_list,
    'tmpfolder' => xhprof_dir
));

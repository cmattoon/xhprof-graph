<?php
require_once('inc/config.php');

$list = new FilesList(Config::$xhdata);
$action = _post('action');
$deleted = false;
$error = false;

if ($action == 'delete') {
  $filesToRemove = array();
  $removed = array();
  $failed = array();
  $error = 'No filenames provided';

  if (isset($_POST['filenames']) && is_array($_POST['filenames'])) {  
    $error = '';
    foreach ($_POST['filenames'] as $basename) {
      $basename = basename($basename);
      $filesToRemove[] = $basename;
      $fpath = Config::$xhdata . "{$basename}.xhprof";
      
      if (file_exists($fpath)) {
	if (unlink($fpath)) {
	  $removed[] = $basename;
	} else {
	  $failed[] = $basename;
	}
      }
    }
  }
  if (count($failed)) {
    $error = "Failed to remove ".count($failed)." files:<ul>";
    foreach ($failed as $file) {
      $error .= "<li>{$file}</li>";
    }
    $error .= "</ul>";
  } else {
    $error = "Deleted " . count($removed) . " files";
  }
  
}

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
/**
 * Looks like this isn't working as expected. 
 * Either my query, or the runId/time mixup in the filename splitting.
 */
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
    'nfiles' => count($file_list),
    'tmpfolder' => Config::$xhdata,
    'error' => $error,
    'deleted' => $deleted
));

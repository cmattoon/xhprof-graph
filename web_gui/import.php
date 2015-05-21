<?php
require_once('inc/config.php');
@ini_set('max_execution_time', 0);

$files = array();
$xhdir=Config::$xhdata;
if (isset($_POST['filenames'])) {
    foreach ($_POST['filenames'] as $file) {
	$file = basename($file);
	if (file_exists($xhdir . "{$file}.xhprof")) {
	    $files[] = "{$file}.xhprof";
	}
    }
}
error_log(json_encode($files));
$result = array();
foreach ($files as $file) {
    error_log("Making graph..");
    $import = new XhprofImport($file);
    if (($res = $import->makeGraph())) {
        error_log("Created graph");
    } else {
        error_log("No graph");
    }
    
    $result[$file] = $res;
}

$tpl = new Template();
$tpl->displayPage(array('files' => $files, 'result' => $result));


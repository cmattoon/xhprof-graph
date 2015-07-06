<?php
require_once('inc/config.php');
// @todo - fix this
@ini_set('max_execution_time', 0);

$files = array();
$xhdir = Config::$xhdata;
if (isset($_POST['filenames'])) {
    foreach ($_POST['filenames'] as $file) {
	$file = basename($file);
	if (file_exists($xhdir . "{$file}.xhprof")) {
	    $files[] = "{$file}.xhprof";
	}
    }
}

$result = array();
foreach ($files as $file) {
    $import = new XhprofImport($file);
    $res = $import->makeGraph();    
    $result[$file] = $res;
}

$tpl = new Template();
$tpl->displayPage(array('files' => $files, 'result' => $result));


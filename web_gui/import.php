<?php
require_once('../src/config.php');
$files = array();

if (isset($_POST['filenames'])) {
    foreach ($_POST['filenames'] as $file) {
	$file = basename($file);
	if (file_exists($settings->get('xhprof_dir') . "{$file}.xhprof")) {
	    $files[] = "{$file}.xhprof";
	}
    }
}
$result = array();
foreach ($files as $file) {
    $import = new XhprofImport($file);
    $import->makeGraph();
    $result[] = $file;
}

$tpl = new Template();
$tpl->displayPage(array('files' => $files, 'result' => $result));


<?php
require_once('inc/config.php');
$msgs = array();

if (isset($_FILES['xhprof_file'])) {
    $file = $_FILES['xhprof_file'];
    if ($file['error'] === 0) {
	$newfile = "/tmp/xhdata/{$file['name']}";
	if (move_uploaded_file($file['tmp_name'], $newfile)) {
	    $msgs[] = "File moved to {$newfile}";

	    $parser = new NewParser($file['name']);
	    $parser->makeGraph();
	    $msgs[] = "Imported {$file['name']} successfully";
	} else {
	    $msgs[] = "Can't move file from {$file['tmp_name']} to {$newfile}";
	}
    }
}
$tpl = new Template();

$tpl->displayPage(array('msgs'=>$msgs));
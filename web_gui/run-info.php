<?php
require_once('inc/config.php');
$client = get_client();
$run = _get('run');
$runs = array();
$script = '';

if ($run) {
    $ql = new QueryList();
    $runs = $ql->getRunStats($run);
    $script = $ql->getScriptNameFromRunId($run);
}

$tpl = new Template();
$tpl->displayPage(array('runs' => $runs, 'runId' => $run, 
			'bcrun' => $run, 'bcscript' => $script));
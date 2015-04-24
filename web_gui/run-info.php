<?php
require_once('inc/config.php');
$client = get_client();
$runId = _get('run');
$runs = array();
$script = '';

if ($runId) {
    $ql = new QueryList();
    $run = new Run($runId);
    $runs = $run->getRunStats();
    $script = $ql->getScriptNameFromRunId($runId);
}

$tpl = new Template();
$tpl->displayPage(array('runs' => $runs, 'runId' => $runId, 
			'bcrun' => $runId, 'bcscript' => $script));
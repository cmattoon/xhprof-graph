<?php
require_once('inc/config.php');
$client = get_client();
$runId = _get('run');
$runs = array();
$script = '';

if ($runId) {
    $ql = new QueryList();
    $run = new Run($runId);
    $runs = $run->getRunStats(true, array('r.wt'=>'desc','r.cpu'=>'desc'));
    $script = $ql->getScriptNameFromRunId($runId);
    $jsonWt = $run->getJSONForPieChart('wt');
    $jsonCpu = $run->getJSONForPieChart('cpu');
    $jsonMu = $run->getJSONForPieChart('mu');
    $jsonPmu = $run->getJSONForPieChart('pmu');
}

$tpl = new Template();
$tpl->displayPage(array('runs' => $runs, 'runId' => $runId, 
			'bcrun' => $runId, 'bcscript' => $script,
			'jsonWt' => $jsonWt, 'jsonCpu' => $jsonCpu,
			'jsonMu' => $jsonMu, 'jsonPmu' => $jsonPmu));

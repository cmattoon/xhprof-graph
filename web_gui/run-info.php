<?php
require_once('inc/config.php');
$client = get_client();
$run = _get('run');
$runs = array();

if ($run) {
    $tmpCrutch = new QueryList();
    $runs = $tmpCrutch->getRunStats($run);
}
$tpl = new Template();
$tpl->displayPage(array('runs' => $runs, 'runId' => $run));
<?php
require_once('../src/config.php');
$cmd = (isset($_GET['cmd']) ? $_GET['cmd'] : '');
$cmd = explode(" ", $cmd);
$cmd = $cmd[0];
$output = "Command '{$cmd}' not recognized";

die(json_encode(array(
    'output' => $output,
    'cssClass' => 'jt-error')));
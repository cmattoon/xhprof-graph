<?php
$loader = require_once('../vendor/autoload.php');
error_reporting(E_ALL);

define('SRC', realpath(dirname(__FILE__) . '/'));
define('ROOT', realpath(SRC . '/../'));
define('WEBROOT', ROOT . '/web_gui/');
define('CONFIG', ROOT . '/config.ini');
$settings = new Settings();
$config = $settings->load(CONFIG);
if (empty($config)) {
    die("Failed to load file at " . CONFIG);
}

foreach ($config as $k=>$v) {
    define($k, $v);
}
require_once('functions.php');

function get_client() {
    $client = new Everyman\Neo4j\Client(DB_HOST, DB_PORT);
    $client->getTransport()->setAuth(DB_USER, DB_PASS);
    return $client;
}

set_exception_handler(function($e) {
    $trace = $e->getTraceAsString();
    $report = base64_encode(json_encode($e->getTrace()));
    
    $html = '<html style="background:#02004A;color:#ccc;font-size:13px;'.
	    'font-family:monospace;white-space:pre-wrap">';
    $html .= "<h1>Caught Exception:</h1>";
    /*
    $html .= "<form action='' method='post'>";
    $html .= "<fieldset><legend>Send Report</legend>";
    $html .= "<input type='hidden' name='b64data' value='{$report}'>";
    $html .= "<input type='submit' value='Send Crash Report'></fieldset>";
    $html .= "</form>";
    */
    $html .= "<h3 style='color:#fff;border-bottom:1px solid #fff;'>Stack Trace</h3>";
    $html .= "<div style='color:#eee;'>{$trace}</div>";
    $html .= "<h3 style='color:#fff;border-bottom:1px solid #fff;'>Message</h3>";
    $html .= $e->getMessage();
    $html .= '</div>';
    echo $html;
    return true;
});
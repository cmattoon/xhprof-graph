<?php

$loader = require_once('vendor/autoload.php');
define('ROOT', realpath(__DIR__ . '/../') . '/');
define('CONFIG', ROOT . 'config.ini');
$config = Settings::load(CONFIG);

foreach ($config as $k=>$v) {
    define($k, $v);
}
require_once('functions.php');
function get_client() {
    $client = new Everyman\Neo4j\Client(DB_HOST, DB_PORT);
    $client->getTransport()->setAuth(DB_USER, DB_PASS);
    return $client;
}
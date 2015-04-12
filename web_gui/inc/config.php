<?php

$loader = require_once('vendor/autoload.php');

$config = array(
    'DB_HOST' => 'localhost',
    'DB_PORT' => 7474,
    'DB_USER' => 'neo4j',
    'DB_PASS' => 'password',
    'XHPROF_LIB' => '../../xhprof/',
    'XHPROF_DIR' => '/tmp/xhdata/'
    
);
foreach ($config as $k=>$v) {
    define($k, $v);
}
function get_client() {
    $client = new Everyman\Neo4j\Client(DB_HOST, DB_PORT);
    $client->getTransport()->setAuth(DB_USER, DB_PASS);
    return $client;
}
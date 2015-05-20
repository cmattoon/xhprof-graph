<?php
/**
 * Global configuration file.
 */
define('DEV_MODE', true);
ini_set('display_errors', DEV_MODE);
error_reporting(E_ALL);

define('ROOT', __DIR__ . '/');
define('SRC', ROOT . 'src/');
define('WEBROOT', ROOT . 'web_gui/');

require_once(ROOT . 'vendor/autoload.php');
require_once(SRC . 'functions.php');


function get_client() {
    $client = new Everyman\Neo4j\Client(Config::$val['db']['host'], Config::$val['db']['port']);
    $client->getTransport()->setAuth(Config::$val['db']['user'], Config::$val['db']['pass']);
    return $client;
}

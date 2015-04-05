<?php
$loader = require('vendor/autoload.php');
define('DB_HOST', 'localhost');
define('DB_PORT', 7474);
define('DB_USER', 'neo4j'); // The username for the Neo4j instance
define('DB_PASS', 'password');

$xhprof_file = '5518a72535298.benchmark_1427679013.xhprof';

try {
    $client = new Everyman\Neo4j\Client(DB_HOST, DB_PORT);
    /**
     * Newer versions of Neo4J require HTTP Basic Auth.
     * Do that here.
     */
    $client->getTransport()->setAuth(DB_USER, DB_PASS);

    // See dir property in Parser class
    $parser = new Parser($file);
    
    // Do things
    $parser->makeGraph();

} catch (Exception $e) {
    echo $e->getMessage();
}
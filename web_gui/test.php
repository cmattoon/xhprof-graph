<?php
$loader = require('vendor/autoload.php');
define('DB_HOST', 'localhost');
define('DB_PORT', 7474);
define('DB_USER', 'neo4j'); // The username for the Neo4j instance
define('DB_PASS', 'password');


NewParser::importAll('/tmp/xhdata/');
die('DONE HERE<hr>');
$file = '';
if (!isset($_GET['f'])) {
    foreach(glob("/tmp/xhdata/*.xhprof") as $f) {
	echo "<div><a href='{$_SERVER['REQUEST_URI']}?f={$f}'>{$f}</a></div>\n";
    }
} else {
    $file = basename($_GET['f']);
}

try {
    $client = new Everyman\Neo4j\Client(DB_HOST, DB_PORT);
    /**
     * Newer versions of Neo4J require HTTP Basic Auth.
     * Do that here.
     */
    $client->getTransport()->setAuth(DB_USER, DB_PASS);
    echo "<div>Parsing {$file}</div>\n";

    // See dir property in Parser class
    $parser = new NewParser($file);
    
    // Do things
    $parser->makeGraph();

} catch (Exception $e) {
    echo $e->getMessage();
}
echo "<hr>Done\n";
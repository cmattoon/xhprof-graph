<?php
require_once('inc/config.php');
$client = get_client();
$query = _post('query');
$result = array();
if ($query) {
    $q = new Everyman\Neo4j\Cypher\Query($client, $query);
    $res = $q->getResultSet();
    foreach ($res as $row) {
        $result[] = $row;
    }
}

$tpl = new Template();
$tpl->displayPage(array('result' => $result));

<?php
/**
 * Ajax file for 'settings' page.
 */
$errors = array();
$data = null;

switch (_post('action')) {
    case 'wipe_all':
        $data = wipe_everything();
        break;
    default:
        $data = null;
        $errors[] = "Invalid action";
        break;
}
/**
 * Removes all nodes and relationships from the database.
 */
function wipe_everything() {
    $client = get_client();
    $query = new Everyman\Neo4j\Cypher\Query($client, "MATCH (n) OPTIONAL MATCH (n)-[r]-() DELETE n,r");
    $query->getResultSet();
    return true;
}


$ajax = new AjaxResponse($data, $errors);
die($ajax->output());
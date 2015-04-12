<?php
require_once('inc/config.php');
$client = get_client();
$class = _get('class');
$method = _get('method');

$data = array();

if ($method) {
    $query = "MATCH (n:Callable)<-[r:called]-(m) 
WHERE (HAS(n.name) AND HAS(n.class) AND n.class = {class} AND n.name = {name})
RETURN n.class, n.name, r.wt, r.ct, r.cpu, r.mu, r.pmu
ORDER BY r.wt DESC, r.cpu DESC";

    $q = new Everyman\Neo4j\Cypher\Query($client, $query, 
					 array(
					     'class' => $class,
					     'name' => $method));
    $res = $q->getResultSet();
    foreach ($res as $r) {
	$data[] = array(
	    'class' => $r['n.class'],
	    'name' => $r['n.name'],
	    'wt' => $r['r.wt'],
	    'cpu' => $r['r.cpu'],
	    'mu' => $r['r.mu'],
	    'pmu' => $r['r.pmu']
	);
    }
}


$tpl = new Template();
$tpl->displayPage(array('data' => $data, 'method' => $method, 'class' => $class));
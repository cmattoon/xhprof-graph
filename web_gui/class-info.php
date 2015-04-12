<?php
require_once('inc/config.php');
$client = get_client();
$class = _get('class');
$data = array();

if ($class) {
    $query = "MATCH (n:Callable)<-[r:called]-(m) 
WHERE (HAS(n.name) AND HAS(n.class) AND n.class = {class}) 
RETURN n.class, n.name, AVG(r.wt),AVG(r.ct),AVG(r.cpu) 
ORDER BY AVG(r.wt) DESC, AVG(r.cpu) DESC";

    $q = new Everyman\Neo4j\Cypher\Query($client, $query, 
					 array('class' => $class));
    $res = $q->getResultSet();
    foreach ($res as $r) {
	$data[] = array(
	    'class' => $r['n.class'],
	    'name' => $r['n.name'],
	    'wt' => $r['AVG(r.wt)'],
	    'cpu' => $r['AVG(r.cpu)']
	);
    }
}


$tpl = new Template();
$tpl->displayPage(array('data' => $data, 'class' => $class));
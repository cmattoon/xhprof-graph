<?php
require_once('inc/config.php');
$client = get_client();
$query = "MATCH (n:Callable)<-[r:called]-(m) 
WHERE (HAS(n.name) AND HAS(n.class) AND n.class <> '') 
RETURN n.class, n.name, AVG(r.wt),AVG(r.ct),AVG(r.cpu) 
ORDER BY AVG(r.wt) DESC, AVG(r.cpu) DESC";

$q = new Everyman\Neo4j\Cypher\Query($client, $query);
$res = $q->getResultSet();

$classes = array();
foreach ($res as $r) {
    $classes[] = array(
	'class' => $r['n.class'],
	'name' => $r['n.name'],
	'wt' => $r['AVG(r.wt)'],
	'cpu' => $r['AVG(r.cpu)']
    );
}

$tpl = new Template();
$tpl->displayPage(array('classes' => $classes));
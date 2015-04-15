<?php
require_once('inc/config.php');
$client = get_client();
$query = "MATCH (n:Callable)<-[r:called]-(m) 
WHERE (HAS(n.name) AND HAS(n.class) AND n.class <> '') 
RETURN n.class, n.name, AVG(r.wt),SUM(r.ct),AVG(r.cpu),AVG(r.mu),AVG(r.pmu)
ORDER BY AVG(r.cpu) DESC, AVG(r.wt) DESC";

$q = new Everyman\Neo4j\Cypher\Query($client, $query);
$res = $q->getResultSet();

$classes = array();
foreach ($res as $r) {
    $classes[] = array(
	'class' => $r['n.class'],
	'name' => $r['n.name'],
	'ct' => $r['SUM(r.ct)'],
	'wt' => $r['AVG(r.wt)'],
	'cpu' => $r['AVG(r.cpu)'],
	'mu' => $r['AVG(r.mu)'],
	'pmu' => $r['AVG(r.pmu)']
    );
}

$tpl = new Template();
$tpl->displayPage(array('classes' => $classes));
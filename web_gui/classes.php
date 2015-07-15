<?php
require_once('inc/config.php');
$client = get_client();
/**
 * This query returns a list of classes and methods, along with total/avg stats.
 * 
 */
$qstr = "
MATCH (n:Callable)-[r:called]->(m)
WHERE (HAS(n.name) and n.name <> 'main()')
RETURN 
    DISTINCT n.class,
    COLLECT (DISTINCT n.name) AS methods,
    COUNT(r) AS num_calls,
    AVG(r.wt) AS avg_wt,
    AVG(r.cpu) AS avg_cpu,
    AVG(r.mu) AS avg_mu,
    AVG(r.pmu) AS avg_pmu
ORDER BY n.class ASC";

$query = new Everyman\Neo4j\Cypher\Query($client, $qstr);

$res = $query->getResultSet();
//var_dump($res);
$classes = array();
foreach ($res as $r) {
    $methods = array();
    foreach ($r['methods'] as $m) {
        $methods[] = $m;
    }
    natcasesort($methods);
    $classes[] = array(
	'class' => $r['n.class'],
        'nmethods' => sizeof($methods),
        'methods' => $methods,
	'total_ct' => $r['num_calls'],
        'avg_wt' => $r['avg_wt'],
        'avg_cpu' => $r['avg_cpu'],
        'avg_mu' => $r['avg_mu'],
        'avg_pmu' => $r['avg_pmu']
    );
}

$tpl = new Template();
$tpl->displayPage(array('classes' => $classes));
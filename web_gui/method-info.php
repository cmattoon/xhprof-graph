<?php
require_once('inc/config.php');
$client = get_client();
$class = _get('class');
$method = _get('method');
$run = _get('run');

$data = array();
$ql = new QueryList();
if ($method) {
    $query = "MATCH (n:Callable)<-[r:called]-(m) 
WHERE (HAS(n.name) AND HAS(n.class) AND n.class = {class} AND n.name = {name})
RETURN n.class, n.name, r.wt, r.ct, r.cpu, r.mu, r.pmu,r.runId
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
	    'run' => $r['r.runId'],
	    'wt' => $r['r.wt'],
	    'cpu' => $r['r.cpu'],
	    'mu' => $r['r.mu'],
	    'pmu' => $r['r.pmu']
	);
    }
}


$tpl = new Template();
$tpl->displayPage(array('data' => $data, 
			'method' => $method, 
			'class' => $class,
			'bcclass' => $class,
			'bcmethod' => $method,
			'run' => $run,
			'bcrun' => $run
));
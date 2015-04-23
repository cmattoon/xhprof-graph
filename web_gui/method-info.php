<?php
require_once('inc/config.php');
$client = get_client();
$class = _get('class');
$method = _get('method');
$run = _get('run');


$obj = new Method("{$class}::{$method}");
$data = $obj->getData($run);

$tpl = new Template();
$tpl->displayPage(array('data' => $data, 
			'method' => $method, 
			'class' => $class,
			'bcclass' => $class,
			'bcmethod' => $method,
			'run' => $run,
			'bcrun' => $run
));
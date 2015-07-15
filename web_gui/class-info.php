<?php
require_once('inc/config.php');
$client = get_client();
$class_name = _get('class');

$klass = new Klass($class_name);
$data = $klass->getClassStats();

$tpl = new Template();
$tpl->displayPage(array(
    'data' => $data, 
    'class' => $class_name, 
    'bcclass' => $class_name, 
    'bcmethod'=>'*',
    'jsonWt' => $klass->getJsonForField('wt'),
    'jsonCpu' => $klass->getJsonForField('cpu'),
    'jsonMu' => $klass->getJsonForField('mu'),
    'jsonPmu' => $klass->getJsonForField('pmu')
));
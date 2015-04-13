<?php
require_once('inc/config.php');
$settings = Settings::load();
$data = array();
if (_post('action') == 'save') {
    foreach ($_POST as $k => $v) {
	if (strpos($k, 'cfgval_') === 0) {
	    $key = str_replace('cfgval_','', $k);
	    Settings::set($key, $v);
	    unset($_POST[$k]);
	}
    }
    Settings::save();

}

$tpl = new Template();
$tpl->displayPage(array('settings'=> $settings));
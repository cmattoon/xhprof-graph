<?php
require_once('inc/config.php');

$page = basename(_request('page'));
$ajax_file = "ajax/{$page}.php";

if (file_exists($ajax_file)) {
    require_once($ajax_file);
}

$ajax = new AjaxResponse(null, array('Invalid AJAX request.'));
die($ajax->output());
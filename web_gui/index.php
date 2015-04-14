<?php
require_once('inc/config.php');
$msgs = array();
$tpl = new Template();
$tpl->displayPage(array('msgs'=>$msgs));
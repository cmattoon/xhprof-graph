<?php
function getTemplate() {
    $loader = new Twig_Loader_Filesystem(DIR_TPL);
    $tpl = new Twig_Environment($loader, array(
	'cache' => '/tmp/tplcache/'
    ));
}
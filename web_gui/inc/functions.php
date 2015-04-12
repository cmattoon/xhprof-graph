<?php
function getTemplate() {
    $loader = new Twig_Loader_Filesystem(DIR_TPL);
    $tpl = new Twig_Environment($loader, array(
	'cache' => '/tmp/tplcache/'
    ));
}
function _post($key, $default='') {
    return (isset($_POST[$key])) ? $_POST[$key] : $default;
}
function _request($key, $default='') {
    return (isset($_REQUEST[$key])) ? $_REQUEST[$key] : $default;
}
function _get($key, $default='') {
    return (isset($_GET[$key])) ? $_GET[$key] : $default;
}
function class_link($class) {
    return "<a href=\"class-info.php?class={$class}\">{$class}</a>";
}

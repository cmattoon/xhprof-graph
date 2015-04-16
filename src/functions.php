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

function get_breadcrumb($script='', $run='', $class='', $method='') {
    $html = array("<ul class=\"uk-breadcrumb\">");
    if (empty($script)) {
	$script = "<span>All Scripts</span>";
    } else {
	$script = "<a href=\"script-info.php?script={$script}\">{$script}</a>"; 
    }
    $html[] = "<li>{$script}</li>";
    if (empty($run)) {
	$run = "<span>All Runs</span>";
    } else {
	$run = "<a href\"run-info.php?run={$run}\">{$run}</a>";
    }
    $html[] = "<li>{$run}</li>";
    if (!empty($class)) {
	$html[] = "<li><a href=\"class-info.php?class={$class}\">{$class}</a></li>";
    }
    if (!empty($method)) {
	$html[] = "<li><a href=\"method-info.php?class={$class}".
		  "&method={$method}}\">{$method}</a></li>";
    }
    $html[] = "</ul>";
    return implode("\n", $html);
}
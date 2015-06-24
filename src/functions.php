<?php
function die_json($json) {
    die(json_encode($json));
}
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


set_exception_handler(function($e) {
    $trace = $e->getTraceAsString();
    $report = base64_encode(json_encode($e->getTrace()));
    
    $html = '<html style="background:#02004A;color:#ccc;font-size:13px;'.
	    'font-family:monospace;white-space:pre-wrap">';
    $html .= "<h1>Caught Exception:</h1>";
    /*
    $html .= "<form action='' method='post'>";
    $html .= "<fieldset><legend>Send Report</legend>";
    $html .= "<input type='hidden' name='b64data' value='{$report}'>";
    $html .= "<input type='submit' value='Send Crash Report'></fieldset>";
    $html .= "</form>";
    */
    $html .= "<h3 style='color:#fff;border-bottom:1px solid #fff;'>Message</h3>";
    $html .= "<div style='color:#333;background:rgba(132, 255, 0, 0.8);padding:1em;'>{$e->getMessage()}</div>";

    $html .= "<h3 style='color:#fff;border-bottom:1px solid #fff;'>Stack Trace</h3>";
    $html .= "<div style='color:#eee;'>{$trace}</div>";
    $html .= '</div>';
    echo $html;
    return true;
});
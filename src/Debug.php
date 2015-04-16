<?php
/**
 * Debug footer
 *
 */
function debug_footer() {
    $html = array(
	"<table width='100%' style='position:fixed;height:75px;bottom:0;left:0;right:0;background-color:black'>");
    foreach ($_SERVER as $k => $v) {
	$html[] = "  <tr><td width='25%'>{$k}</td><td width='75%'>{$v}</td></tr>";
    }
    $html[] = "</table>";
    return $html;
}

debug_footer();
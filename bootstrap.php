<?php
/**
 * Global configuration file.
 */
define('DEV_MODE', true);
ini_set('display_errors', DEV_MODE);
error_reporting(E_ALL);

define('ROOT', __DIR__ . '/');
define('SRC', ROOT . 'src/');
define('WEBROOT', ROOT . 'web_gui/');

require_once(ROOT . 'vendor/autoload.php');
require_once(SRC . 'functions.php');

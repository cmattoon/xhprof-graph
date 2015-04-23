<?php
if (file_exists('install.php')) {
    require_once('install.php');
} else {
    require_once('../src/config.php');
}
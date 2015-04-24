<?php
/**
 * Gets files from /tmp/xhprof or wherever ($config['xhprof_dir'])
 */
class FilesList {
    public $dir = '';

    public function __construct($dir='') {
	$dir = ($dir) ? $dir : Config::$xhdata;
	if (is_dir($dir) && is_readable($dir)) {
	    $this->dir = realpath($dir);
	}
    }
    
    public function getFiles($filter='*.xhprof') {
	return glob("{$this->dir}/{$filter}");
    }
}
<?php
/**
 * Wrapper for Twig
 */
class Template {
    public $cacheDir = '/tmp/tplcache/';
    public $templateDir = 'tpl/';
    public $page = '';

    public function __construct($page='') {
	$this->cacheDir = false;
	$this->_loader = new Twig_Loader_Filesystem($this->templateDir);
	$this->_twig = new Twig_Environment($this->_loader, array(
	    'cache' => $this->cacheDir));
	$this->page = ($page) ? $page : $_SERVER['SCRIPT_FILENAME'];
	$this->page = basename($this->page);
    }
    
    public function displayPage($vars=array()) {
	$tpl = str_replace(".php", '.html', $this->page);
	echo $this->_twig->render('_header.html');
	echo $this->_twig->render($tpl, $vars);
	echo $this->_twig->render('_footer.html');
    }
}
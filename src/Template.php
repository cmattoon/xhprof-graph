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
	$vars['allow_csv'] = true;
	$tpl = str_replace(".php", '.html', $this->page);
        /* If 'localhost', change to SERVER_ADDR */
        $neo4j_host = (Config::$val['db']['host'] == 'localhost') ? $_SERVER['SERVER_ADDR'] : 
            Config::$val['db']['host'];
        $vars['neo4j_url'] = "http://{$neo4j_host}:" . Config::$val['db']['port'];
        $vars['menu'] = $this->_getMenuItems();
	echo $this->_twig->render('_header.html', $vars);
	echo $this->_twig->render('_breadcrumbs.html', $vars);
	echo $this->_twig->render($tpl, $vars);
	echo $this->_twig->render('_footer.html');
    }

    protected function _getMenuItems() {
        $menu = array(
            array(
                'href' => 'index.php',
                'name' => 'Home',
                'icon' => 'home',
                'active' => ''
            ),
            array(
                'href' => 'runs.php',
                'name' => 'Runs',
                'icon' => 'bar-chart',
                'active' => ''
            ),
            array(
                'href' => 'classes.php',
                'name' => 'Classes',
                'icon' => 'bar-chart',
                'active' => ''
            ),
            array(
                'href' => 'reports.php',
                'name' => 'Reports',
                'icon' => 'bar-chart',
                'active' => ''
            ),
            array(
                'href' => 'files.php',
                'name' => 'Files',
                'icon' => 'folder',
                'active' => ''
            ),
            array(
                'href' => 'settings.php',
                'name' => 'Settings',
                'icon' => 'cog',
                'active' => ''
            ),
        );
        $current_page = basename($_SERVER['SCRIPT_NAME']);
        foreach ($menu as $idx => $item) {
            if ($item['href'] == $current_page) {
                $menu[$idx]['active'] = 'uk-active';
                break;
            }
        }
        return $menu;
    }
}
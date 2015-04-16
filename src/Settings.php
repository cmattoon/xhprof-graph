<?php
/**
 * A quick settings module.
 */
class Settings {
    protected static $settings = array();

    public function load($file='', $force=false) {
	if (empty(self::$settings)) {
	    if ($file) {
		self::$settings = parse_ini_file($file);
	    }
	}
	return self::$settings;
    }
    public function get($key) {
	return (isset(self::$settings[$key])) ? self::$settings[$key] : '';
    }
    public function set($key, $val) {
	self::$settings[$key] = $val;
    }
    public static function save() {
	$assoc_arr = self::$settings;
	$path = CONFIG;

	$content = ""; 

        foreach (self::$settings as $key => $elem) { 
            if(is_array($elem)) {
		for($i=0;$i<count($elem);$i++) {
                    $content .= $key."[] = \"".$elem[$i]."\"\n"; 
                } 
            } else if ($elem=="") {
		$content .= $key." = \n"; 
	    } else {
		$content .= $key." = \"".$elem."\"\n"; 
	    }
        } 
	if (!$handle = fopen($path, 'w')) { 
            return false; 
	}
	
	$success = fwrite($handle, $content);
	fclose($handle); 
	
	return $success; 
    }
}
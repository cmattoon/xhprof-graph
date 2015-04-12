<?php
//
//  Copyright (c) 2009 Facebook
//
//  Licensed under the Apache License, Version 2.0 (the "License");
//  you may not use this file except in compliance with the License.
//  You may obtain a copy of the License at
//
//      http://www.apache.org/licenses/LICENSE-2.0
//
//  Unless required by applicable law or agreed to in writing, software
//  distributed under the License is distributed on an "AS IS" BASIS,
//  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
//  See the License for the specific language governing permissions and
//  limitations under the License.
//

//
// This file defines the interface iXHProfRuns and also provides a default
// implementation of the interface (class XHProfRuns).
//

/**
 * iXHProfRuns interface for getting/saving a XHProf run.
 *
 * Clients can either use the default implementation,
 * namely XHProfRuns_Default, of this interface or define
 * their own implementation.
 *
 * @author Kannan
 */
interface iXHProfRuns {

  /**
   * Returns XHProf data given a run id ($run) of a given
   * type ($type).
   *
   * Also, a brief description of the run is returned via the
   * $run_desc out parameter.
   */
  public function get_run($run_id, $type, &$run_desc);

  /**
   * Save XHProf data for a profiler run of specified type
   * ($type).
   *
   * The caller may optionally pass in run_id (which they
   * promise to be unique). If a run_id is not passed in,
   * the implementation of this method must generated a
   * unique run id for this saved XHProf run.
   *
   * Returns the run id for the saved XHProf run.
   *
   */
  public function save_run($xhprof_data, $type, $run_id = null);
}


/**
 * XHProfRuns_Default is the default implementation of the
 * iXHProfRuns interface for saving/fetching XHProf runs.
 *
 * It stores/retrieves runs to/from a filesystem directory
 * specified by the "xhprof.output_dir" ini parameter.
 *
 * @author Kannan
 */
class XHProfRuns_Default implements iXHProfRuns {

  private $dir = '';
  private $suffix = 'xhprof';

  private function gen_run_id($type) {
    return uniqid();
  }

  private function file_name($run_id, $type) {

    $file = "$run_id.$type." . $this->suffix;

    if (!empty($this->dir)) {
      $file = $this->dir . "/" . $file;
    }
    return $file;
  }

  public function __construct($dir = null) {

    // if user hasn't passed a directory location,
    // we use the xhprof.output_dir ini setting
    // if specified, else we default to the directory
    // in which the error_log file resides.

    if (empty($dir)) {
      $dir = ini_get("xhprof.output_dir");
      if (empty($dir)) {

        $dir = sys_get_temp_dir();

        xhprof_error("Warning: Must specify directory location for XHProf runs. ".
                     "Trying {$dir} as default. You can either pass the " .
                     "directory location as an argument to the constructor ".
                     "for XHProfRuns_Default() or set xhprof.output_dir ".
                     "ini param.");
      }
    }
    $this->dir = $dir;
  }

  public function get_run($run_id, $type, &$run_desc) {
    $file_name = $this->file_name($run_id, $type);

    if (!file_exists($file_name)) {
      xhprof_error("Could not find file $file_name");
      $run_desc = "Invalid Run Id = $run_id";
      return null;
    }

    $contents = file_get_contents($file_name);
    $run_desc = "XHProf Run (Namespace=$type)";
    return unserialize($contents);
  }

  public function save_run($xhprof_data, $type, $run_id = null) {

    // Use PHP serialize function to store the XHProf's
    // raw profiler data.
    $xhprof_data = serialize($xhprof_data);

    if ($run_id === null) {
      $run_id = $this->gen_run_id($type);
    }

    $file_name = $this->file_name($run_id, $type);
    $file = fopen($file_name, 'w');

    if ($file) {
      fwrite($file, $xhprof_data);
      fclose($file);
    } else {
      xhprof_error("Could not open $file_name\n");
    }

    // echo "Saved run in {$file_name}.\nRun id = {$run_id}.\n";
    return $run_id;
  }

  function list_runs() {
    if (is_dir($this->dir)) {
        echo "<hr/>Existing runs:\n<ul>\n";
        $files = glob("{$this->dir}/*.{$this->suffix}");
        usort($files, create_function('$a,$b', 'return filemtime($b) - filemtime($a);'));
        foreach ($files as $file) {
            list($run,$source) = explode('.', basename($file));
            echo '<li><a href="' . htmlentities($_SERVER['SCRIPT_NAME'])
                . '?run=' . htmlentities($run) . '&source='
                . htmlentities($source) . '">'
                . htmlentities(basename($file)) . "</a><small> "
                . date("Y-m-d H:i:s", filemtime($file)) . "</small></li>\n";
        }
        echo "</ul>\n";
    }
  }
}









































/**
 * Another implementation of iXHProfRuns
 * This class works to aggregate overall stats for an application over time.
 * The intended use case is to track per-page performance information as well
 * as average (mean) function-level statistics.
 *
 * RunGroups - Run groups are a collection of run files that are used to
 *     get a more accurate picture of how an application is performing overall.
 *     For example, you might create a "before" and "after" RunGroup to compare
 *     the performance of a routine before and after a change.
 *
 * RunTypes - RunTypes are names for the specific run. They are typically
 *     used to indicate the page that was profiled.
 *
 * @author cmattoon
 */
class XHProfRuns_Aggregator implements iXHProfRuns {

    /**
     * The base directory
     * @var string $dir
     */
    protected $_baseDir = '';
    protected $_suffix = 'xhprof';

    /**
     * A list of known RunGroups (subdirectories)
     * @var $_runGroups array
     */
    protected $_runGroups = array();

    /**
     * Constructor.
     *
     * @param string $dir (optional, default NULL) The root directory
     */
    public function __construct($dir = null) {
	$this->setDir($dir);
    }

    /**
     * jQuery-style getter/setter
     * @param string $dir (optional, default NULL)
     */
    public function dir($dir=null) {
	if ($dir !== null) {
	    $this->setDir($dir);
	}
	return $this->_baseDir;
    }
    
    /**
     * Sets the directory.
     */
    public function setDir($dir=null) {
	if (empty($dir)) {
	    $dir = ini_get("xhprof.output_dir");
	    if (empty($dir)) {
		$dir = sys_get_temp_dir();
		xhprof_error(
		    "Warning: Must specify directory location for XHProf runs. ".
		    "Trying {$dir} as default. You can either pass the " .
		    "directory location as an argument to the constructor ".
		    "for XHProfRuns_Default() or set xhprof.output_dir ".
		    "ini param.");
	    }
	}
	$this->_baseDir = $dir;
    }

    
    private function gen_run_id($type) {
	return uniqid();
    }
    
    /**
     * Returns the (hopefully absolute) path to the file
     *
     * @param string $run_id The Run ID
     * @param string $type The Type
     * @return string
     */
    private function file_name($run_id, $type) {
	
	$file = "$run_id.$type." . $this->_suffix;
	
	if (!empty($this->_baseDir)) {
	    $file = $this->_baseDir . "/" . $file;
	}
	return $file;
    }
    
    /**
     * Gets raw run data for a given run and type
     */
    public function get_run($run_id, $type, &$run_desc) {
	$file_name = $this->file_name($run_id, $type);
	
	if (!file_exists($file_name)) {
	    xhprof_error("Could not find file $file_name");
	    $run_desc = "Invalid Run Id = $run_id";
	    return null;
	}
	
	$contents = file_get_contents($file_name);
	$run_desc = "XHProf Run (Namespace=$type)";
	return unserialize($contents);
    }
    /**
     * Allow grouping results by 'type' (e.g., page) for more accurate
     * reporting. This class attempts to aggregate all the stats, which
     * isn't necessarily useful or desired when a function performs differently.
     * For example, a 'getShippingCost' function might run longer on a shopping
     * cart page than on the product page, since it's dealing with multiple
     * products. 
     */
    public function getRunType() {
	
    }

    /**
     * Saves a run.
     */
    public function save_run($xhprof_data, $type, $run_id = null) {
	
	// Use PHP serialize function to store the XHProf's
	// raw profiler data.
	$xhprof_data = serialize($xhprof_data);
	
	if ($run_id === null) {
	    $run_id = $this->gen_run_id($type);
	}
	
	$file_name = $this->file_name($run_id, $type);
	$file = fopen($file_name, 'w');
	
	if ($file) {
	    fwrite($file, $xhprof_data);
	    fclose($file);
	} else {
	    xhprof_error("Could not open $file_name\n");
	}
	
	return $run_id;
    }

    /**
     * Displays a list of saved runs
     * @todo - get rid of this
     */
    public function list_runs() {
	$runs = array();

	if (!is_dir($this->_baseDir)) {
	    return false;
	}
        $files = glob("{$this->_baseDir}/*.{$this->_suffix}");
        usort($files, create_function('$a,$b', 'return filemtime($b) - filemtime($a);'));
	    
        foreach ($files as $file) {
	    list($run,$source) = explode('.', basename($file));
	    
	    $url = htmlentities($_SERVER['SCRIPT_NAME']).
		   '?run=' . htmlentities($run) . '&source=' . 
		   htmlentities($source);
	    $filename = htmlentities(basename($file));
	    
	    $runs[] = array(
		'run' => $run,
		'source' => $source,
		'filename' => $filename,
		'url' => $url,
		'time' => filemtime($file)
	    );
        }
	return $runs;
    }
}

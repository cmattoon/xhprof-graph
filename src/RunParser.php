<?php
/**
 * This parses an xhprof dump file and provides a way to access the data.
 */
class XHProf_RunParser {
    public $file = '';
    public static $metrics = array(
	'wt' => array('Wall', 'ms', 'walltime'),
	'ut' => array('User', 'ms', 'User CPU Time'),
	'st' => array('Sys', 'ms', 'System CPU Time'),
	'mu' => array('MUse', 'bytes', 'Memory Usage'),
	'pmu' => array('PMUse', 'bytes', 'Peak Memory Usage'),
	'samples' => array('Samples', 'samples', 'CPU Time')
    );
    
    public function __construct($file) {
	if (!(file_exists($file) && is_readable($file))) {
	    throw new InvalidArgumentException("Cant find file {$file}");
	}
	$this->file = $file;
    }

    /**
     * @see xhprof_compute_flat_info
     */
    public function getInfo($raw_data) {
	$incl_times = $this->_calcInclusiveTimes($raw_data);

	/* Total metric value is the value for "main()" */
	foreach ($metrics as $metric) {
	    $this->totals[$metric] = $incl_times["main()"][$metric];
	}
	/* Set exclusive (self) metric value to inclusive value to start
	Add up total number of function calls */
	foreach ($incl_times as $symbol => $info) {
	    foreach ($metrics as $metric) {
		$incl_times[$symbol]["excl_{$metric}"] = $incl_times[$symbol][$metric];
	    }
	    $this->totals['ct'] += $info['ct'];
	}
	/** Excl = excl - (sum(child_incl_times)) */
	foreach ($raw_data as $parent_child => $info) {
	    list($parent, $child) = $this->_parseSymbolName($parent_child);
	    if ($parent) {
		foreach ($metrics as $metric) {
		    if (isset($incl_times[$parent])) {
			$incl_times[$parent]["excl_{$metric}"] -= $info[$metric];
		    }
		}
	    }
	}
	return $incl_times;
    }

    /**
     * Split function name.
     * It's stored as "parent==>child"
     * @example "_parseSymbolName==>explode"
     */
    protected function _parseSymbolName($name) {
	$name = explode('==>', $name);
	return (count($name) > 1) ? $name : array(null, $name[0]);
    }
    /**
     * @see xhprof_compute_inclusive_times
     */
    protected function _calcInclusiveTimes($data) {
	
    }
}
<?php

class Upload {
    public $data = array();
    
    /**
     * @param array $file The $_FILES['whatever'] array
     */
    public function __construct($file) {
	$this->file = $file;
    }
}
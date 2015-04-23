<?php

class XhprofUpload extends Upload {
    
    public function __construct($file) {
	parent::__construct($file);

	if (!$this->file || $this->file['error'] > 0) {
	    throw new Exception("Failed to upload file! (Error: {$this->file['error']})");
	}
    }

    public function save() {
	$newfile = "/tmp/xhdata/{$file['name']}";
	if (move_uploaded_file($this->file['tmp_name'], $newfile)) {
	    return $newfile;
	}
	throw new Exception("Can't move file to {$newfile}! (Permissions/directory exists?)");
    }
}
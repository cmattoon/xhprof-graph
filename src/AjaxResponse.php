<?php
/**
 * A standard format for ajax responses.
 * All responses should contain the following standard keys:
 *     'response' => mixed
 *     'errors' => array
 */
class AjaxResponse {
    public $data = null;
    public $errors = array();

    /**
     * @param mixed $data
     * @param array $errors (default array())
     */
    public function __construct($data, $errors=array()) {
        $this->data = $data;
        $this->errors = (array)$errors;
    }

    /**
     * Dumps the JSON-encoded response.
     * @return string
     */
    public function output() {
        return json_encode(array(
            'response' => $this->data,
            'errors' => $this->errors
        ));
    }
}
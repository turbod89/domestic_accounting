<?php

/**
 *  Author: Daniel Torres
 *
 */

namespace App\Helpers;

class BaseHelper {

    public $errors = [];

    public function hasErrors() {
        return count($this->errors);
    }

    public function getErrors() {
        return $this->errors;
    }

    protected function addError($code, $message, $data) {
        $this->errors[] = [
            'code' => $code,
            'message' => $message,
            'data' => $data,
        ];

        return $this;
    }


}
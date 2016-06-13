<?php

namespace Entity;

/**
 * Represents a response returned by Curl model
 */
class CurlResponse {

    private $content;
    private $errors;

    public function __construct($content, $errors) {
        $this->content = $content;
        $this->errors = $errors;
    }

    public function asJson() {
        if ($this->isValid()) {
            return json_decode($this->content, true);
        }
        return null;
    }

    public function asString() {
        if ($this->isValid()) {
            return $this->content;
        }
        return null;
    }

    public function isValid() {
        return !$this->isEmpty() && !$this->hasErrors();
    }

    public function getErrors() {
        return $this->errors;
    }

    public function hasErrors() {
        return !empty($this->getErrors());
    }

    public function isEmpty() {
        return empty($this->content);
    }

}

<?php

namespace Entity;

/**
 * Represents a match
 *
 * It's useful when a method return a match, and need this match to process another task
 * @see \Model\YouTubeInMp3 for a concrete implementation
 */
class MatchResult {

    private $found;
    private $data;

    public function __construct($found, array $data = []) {
        $this->found = (bool) $found;
        $this->data = $data;
    }

    public function isEmpty() {
        return !$this->found;
    }

    public function getData($key = null) {
        if (isset($key)) {
            if (array_key_exists($key, $this->data)) {
                return $this->data[$key];
            }
            return null;
        }
        return $this->data;
    }

}

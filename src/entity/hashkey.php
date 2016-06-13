<?php

namespace Entity;

/**
 * Used to determinate a constant-time key based on several parameters
 *
 * It is mainly used for cache, as file name depends on what define the content of the cache (e.g. a search query would be the key, and results the content)
 */
class HashKey {

    private $algo;
    private $keys = [];

    public function __construct($algo = 'sha256') {
        $this->algo = $algo;
    }

    // Use this unique $key as hash key
    public function raw($key) {
        $this->keys = $key;
        return $this;
    }

    // Add a key, it can be a nested array as well
    public function add($key) {
        if (!is_array($this->keys)) {
            throw new \Exception('Can\'t add hash key after raw.');
        }
        $this->keys[] = $key;
        return $this;
    }

    // Return hash key by using provided keys
    public function compile() {
        if (is_string($this->keys)) {
            return $this->keys;
        } else {
           return $this->hash($this->keys);
        }
    }

    protected function hash($key) {
        if (is_array($key)) {
            $hash = '$%+';
            foreach ($key as $k) {
                $hash = $hash . $this->hash($k);
            }
            return $this->hash($hash);
        } else {
            return hash($this->algo, '#$%' . $key);
        }
    }

}

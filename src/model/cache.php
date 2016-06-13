<?php

namespace Model;

use \Entity\HashKey;

/**
 * Used to cache json and raw data
 * 
 * It stores and retrieves from cache easily
 * 
 * Uses hashkey to get a file name from several parameters
 * @see \Entity\HashKey
 * 
 * Use either json or raw data (such as img, mp3, ..) for content
 * 
 * The cache is auto-cleared when application ends
 * @see \Model\CacheServiceProvider
 */
class Cache extends Model {
    
    const EXPIRE_MINUTE = 60;
    const EXPIRE_HOUR = 3600;
    const EXPIRE_DAY = 86400;
    const EXPIRE_WEEK = 86400 * 7;
    const EXPIRE_MONTH = 86400 * 30;

    private $folder;
    private $defaultExpiration;
    private $fileExtension;

    // $mamespace is the directory used for cached file
    // $defaultExpiration is self-explanatory
    // $fileExtension is either null (meaning is cache content such as string, array, ..) or 'mp3', 'jpg' for files
    public function __construct($namespace, $defaultExpiration, $fileExtension) {
        $this->folder = PHP_ROOT . 'cache/' . $namespace . '/';
        if (!is_dir(dirname($this->folder))) mkdir(dirname($this->folder));
        if (!is_dir($this->folder)) mkdir($this->folder);
        $this->defaultExpiration = $defaultExpiration;
        $this->fileExtension = empty($fileExtension) ? false : $fileExtension;
    }

    // Return cached element with provided key
    // $default value can be a closure, that is only executed if no cache is found
    public function get(HashKey $key, $default = null) {
        $file = $this->getFile($key);

        // Cache exists
        if (is_file($file)) {

            // Check the modified time
            if ($this->fileExtension) {
                if (filemtime($file) + $this->defaultExpiration > time()) {
                    return $file;
                }

            // Check stored expiration
            } else {
                $json = json_decode(file_get_contents($file), true);
                if ($json['expiration'] > time()) {
                    return $json['content'];
                }
            }

            // Expired :(
            unlink($file);
        }

        // We have a default value
        if (isset($default)) {
            if (is_callable($default)) {
                $data = $default();
                if (!empty($data)) {
                    // Store the result of the closure
                    $this->set($key, $data, $this->defaultExpiration);
                    return $this->fileExtension ? $file : $data;
                }
            } else {
                return $default;
            }
        }

        // Neither cache, nor default
        return null;
    }

    public function set(HashKey $key, $value, $expire = null) {
        $expire = $expire ?: $this->defaultExpiration;
        $file = $this->getFile($key);
        if ($this->fileExtension) {
            // Put the raw file directly
            file_put_contents($file, $value);
        } else {
            // Wrap content is json
            file_put_contents($file, json_encode([
                'expiration' => $expire + time(),
                'content' => $value
            ]));
        }
    }

    public function has(HashKey $key) {
        return $this->get($key) !== null;
    }

    public function delete(HashKey $key) {
        $file = $this->getFile($key);
        if (is_file($file)) {
            unlink($file);
        }
        return false;
    }

    // This remove all expired cached files
    public function clearExpired() {
        foreach (scandir($this->folder) as $file) {
            if ($file !== '.' and $file !== '..') {
                $file = $this->folder . $file;
                if ($this->fileExtension) {
                    if (filemtime($file) + $this->defaultExpiration < time()) {
                        unlink($file);
                    }
                } else {
                    // Depth set to 2 to only decode what we want
                    $json = json_decode(file_get_contents($file), false, 2);
                    if (isset($json->expiration) and $json->expiration < time()) {
                        unlink($file);
                    }
                }
            }
        }
    }

    // Return file path based on HashKey
    private function getFile(HashKey $key) {
        return $this->folder . $key->compile() . '.' . ($this->fileExtension ?: 'json');
    }

}
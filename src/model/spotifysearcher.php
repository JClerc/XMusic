<?php

namespace Model;

use \Silex\Application;
use \Entity\HashKey;

/**
 * Spotify implementation of Music Searcher
 */
class SpotifySearcher extends MusicSearcher {

    const API_ENDPOINT = 'https://api.spotify.com/v1/';
    const RESULTS_PER_PAGE = 20;

    private $curl;
    private $cache;

    public function __construct(Application $app) {
        $this->curl = $app['curl'];
        $this->cache = $app['cache']('spotify', Cache::EXPIRE_HOUR, false);
    }

    // Search a $mode with $query, at $page
    public function search($mode, $query, $page) {
        
        // This is our HashKey for cache
        $hash = (new HashKey())->add('search')->add($mode)->add(strtolower($query))->add($page);

        // Return cached data, and if it doesn't exists, then it execute closure to retrieve new data
        return $this->cache->get($hash, function () use ($mode, $query, $page) {
            return $this->extractSearch($this->fetch(
                'search?q='
                 . urlencode($query)
                 . '&type=' 
                 . $mode 
                 . '&offset=' 
                 . (($page - 1) * self::RESULTS_PER_PAGE)
                 . '&limit=' 
                 . self::RESULTS_PER_PAGE
            ), $mode .'s');
        });
    }

    public function getAlbum($id) {
        return $this->cache->get((new HashKey())->add('album')->add($id), function () use ($id) {
            return $this->fetch('albums/' . $id);
        });
    }

    public function getArtist($id) {
        return $this->cache->get((new HashKey())->add('artist')->add($id), function () use ($id) {
            $artist = $this->fetch('artists/' . $id);
            if (is_array($artist)) {
                // We also need to do a 2nd request to get top tracks
                $tracks = $this->fetch('artists/' . $id . '/top-tracks?country=US');
                if (is_array($tracks)) {
                    return $artist + $tracks;
                }
            }
            return null;
        });
    }

    // Ensure the results exists and is correct
    protected function extractSearch($results, $key) {
        if (!empty($results) and is_array($results) and array_key_exists($key, $results) and array_key_exists('items', $results[$key])) {
            return $results[$key];
        }
        return null;
    }

    protected function fetch($url) {
        return $this->curl->get(self::API_ENDPOINT . $url)->asJson();
    }

}

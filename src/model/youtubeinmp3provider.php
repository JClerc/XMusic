<?php

namespace Model;

use \Silex\Application;
use \Entity\MatchResult;
use \Entity\HashKey;

/**
 * YouTubeInMp3 implementation of Music Provider
 */
class YouTubeInMp3Provider extends MusicProvider {

    const YOUTUBE_ENDPOINT = 'https://www.googleapis.com/youtube/v3/';
    const YOUTUBE_KEY = 'AIzaSyBm8-ybcLp1jvbMyE0V3b1srnYZ1b9ST3I';

    const YOUTUBE_IN_MP3_ENDPOINT = 'https://www.youtubeinmp3.com/fetch/?format=json&video=http://www.youtube.com/watch?v=';

    const CACHE_TRACKS = PHP_ROOT . 'cache/tracks/{id}.mp3';

    private $curl;
    private $cache;

    public function __construct(Application $app) {
        $this->curl = $app['curl'];
        $this->cache = $app['cache']('youtubeinmp3', Cache::EXPIRE_DAY, 'mp3');
    }

    // With a $artist and a $track, we try to get a youtube video
    public function match($artist, $track) {

        // This is volontary not cached, due to requests diversity
        $json = $this->search($artist, $track);

        if (!empty($json) and !empty($json['items'])) {
            $item = reset($json['items']);

            if (!empty($item['id']['videoId'])) {
                // Return results with video id
                return new MatchResult(true, [
                    'id' => $item['id']['videoId']
                ]);
            }
        }

        // Return empty match results to avoid "called method on null"
        return new MatchResult(false);
    }

    // Return from cache
    // It means that the download should have been prepared
    public function get($id) {
        return $this->cache->get((new HashKey())->raw($id));
    }

    // Download a from a match (= video id)
    public function download(MatchResult $matchResult) {

        $id = $matchResult->getData('id');

        // This is cached using the video id as key
        return $this->cache->get((new HashKey())->raw($id), function () use ($id) {

            // Get the download link
            $response = $this->curl->get(self::YOUTUBE_IN_MP3_ENDPOINT . $id);

            if ($response->isValid()) {

                $json = $response->asJson();
                if (isset($json['link'])) {

                    // And download the music !
                    $mp3 = $this->curl->get($json['link']);
                    if ($mp3->isValid()) {
                        $data = $mp3->asString();
                        // At least 100 Ko, as sometimes it returns a html page
                        if (strlen($data) > 100 * 1000) {
                            return $data;
                        }
                    }
                }
            }

            return null;

        });
    }

    protected function search($artist, $track) {
        return $this->curl->get(self::YOUTUBE_ENDPOINT . 'search?part=snippet&q=' .  urlencode($artist . ' - ' . $track) . '&type=video' . '&key=' . self::YOUTUBE_KEY)->asJson();
    }

}

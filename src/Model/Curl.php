<?php

namespace Model;

use \Entity\CurlResponse;

/**
 * Execute GET and POST requests easily
 *
 * It also define many headers to simulate a browser 
 */
class Curl extends Model {

    public function get($url) {
        return $this->curl($url);
    }

    public function post($url, $post) {
        return $this->curl($url, $post);
    }

    protected function curl($url, $post = false, $opts = []) {
        $ch = curl_init();
        
        // Base settings
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        // Simulate browser
        $agent = $_SERVER['HTTP_USER_AGENT'];
        if (empty($agent) or strlen($agent) < 50) $agent = 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/40.0.2214.85 Safari/537.36';

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate,sdch');
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_REFERER, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, $agent);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language: fr-FR,fr;q=0.8,en-US;q=0.6,en;q=0.4',
            'Cache-Control: max-age=0'
        ]);

        // Post request
        if (is_array($post)) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }

        // Custom settings, override others
        foreach ($opts as $key => $value) {
            curl_setopt($ch, $key, $value);
        }

        $result = curl_exec($ch);
        $errors = curl_error($ch);
        curl_close($ch);

        return new CurlResponse($result, $errors);
    }

}

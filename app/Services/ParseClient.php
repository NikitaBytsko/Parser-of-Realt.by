<?php

namespace App\Services;

use GuzzleHttp\Client;

class ParseClient
{
    /**
     * @return Client
     */
    public function create_connection()
    {
        set_time_limit(0);

        $accept = '*/*';
        $referer = 'https://www.google.com/';
        $content_type = 'application/x-www-form-urlencoded';
        $accept_language = 'en-US,en;q=0.8,hi;q=0.6,und;q=0.4';
        $user_agent = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.103 Safari/537.36';

        $headers = [
            'Accept' => $accept,
            'Referer' => $referer,
            'User-Agent' => $user_agent,
            'Content-Type' => $content_type,
            'Accept-Language' => $accept_language,
        ];

        $client = new Client([
            'timeout' => 0,
            'verify' => false,
            'headers' => $headers,
            'connect_timeout' => 0,
        ]);

        return $client;
    }
}

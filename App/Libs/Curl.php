<?php

namespace App\Libs;

class Curl
{
    public static function doPostRequest($url, $postData, $options = [], $params = []) {
        $defaultOptions = [
            CURLOPT_POSTFIELDS     => json_encode($postData),
            CURLOPT_URL            => $url.'?hapikey='.HUBSPOT_API_KEY.'&'.http_build_query($params),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_HTTPHEADER     => [
                'accept: application/json',
                'content-type: application/json',
            ],
        ];
        $curl = curl_init();
        curl_setopt_array($curl, $defaultOptions + $options);
        $response = curl_exec($curl);
        $err = curl_error($curl);

        if ($err) {
            throw new \Exception($err);
        } else {
            return $response;
        }
    }

    public static function doGetRequest($url, $params, $options = []) {
        echo $url.'?hapikey='.HUBSPOT_API_KEY.'&'.http_build_query($params);
        exit();

        $defaultOptions = [
            CURLOPT_URL            => $url.'?hapikey='.HUBSPOT_API_KEY.'&'.http_build_query($params),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'GET',
            CURLOPT_HTTPHEADER     => [
                'accept: application/json',
                'content-type: application/json',
            ],
        ];
        $curl = curl_init();

        curl_setopt_array($curl, $defaultOptions + $options);
        $response = curl_exec($curl);
        $err = curl_error($curl);

        if ($err) {
            throw new \Exception($err);
        } else {
            print_r($response);
            exit();

            return json_decode($response, true);
        }
    }
}

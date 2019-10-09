<?php

class HttpUtils
{

    public static function postJson($url, $json, $timeout = 5)
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_POST => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_SSL_VERIFYPEER => FALSE,
            CURLOPT_HTTPHEADER => array(
                'Content-Type:application/json;charset=utf-8'
            ),
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_CONNECTTIMEOUT => $timeout
        ));

        $output = curl_exec($ch);

        if (curl_errno($ch) !== 0) { // 请求失败
            $curlError = curl_error($ch);
            curl_close($ch);
            throw new Exception('Http request error. ' . $curlError);
        }
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($statusCode !== 200) {
            curl_close($ch);
            throw new Exception('Http request error. Status code: ' . $statusCode);
        }
        curl_close($ch);
        return $output;
    }

}
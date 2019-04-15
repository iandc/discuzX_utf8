<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_blog.php 36278 2016-12-09 07:52:35Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function getStatData($key)
{
    return memory('get', $key);
}

function setStatData($key, $data, $ttl)
{
    memory('set', $key, $data, $ttl);
}

function getStatKey($time = 300)
{
    switch ($time) {
        case 300:
            $key = 'alarm_stat_five_minute';
            break;
        case 600:
            $key = 'alarm_stat_ten_minute';
            break;
        case 1800:
            $key = 'alarm_stat_half_hour';
            break;
        default:
            $key = 'alarm_stat_five_minute';
    }
    return $key;
}

function request_by_curl($remote_server, $post_string)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $remote_server);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json;charset=utf-8'));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // 线下环境不用开启curl证书验证, 未调通情况可尝试添加该代码
    // curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);.
    // curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

function post_alarm($webhook, $message) {
    $data = [
        'msgtype' => 'text',
        'text' => [
            'content' => $message
        ]
    ];
    $result = request_by_curl($webhook, json_encode($data));
    return $result;
}

?>
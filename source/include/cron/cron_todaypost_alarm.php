<?php

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

$webhook = 'https://oapi.dingtalk.com/robot/send?access_token=bca9fb29854748deb3d3670da600dbea220c4bcb1c996bd0d6d10e4432d8238e';

$timeNum = [
    360 => 36,
    420 => 42,
    480 => 48,
    540 => 54,
    600 => 60,
    1800 => 180,
    3600 => 360,
];

$newThreadNum = 10;

require_once libfile('function/alarm');

loadcache('historyposts');
$postdata = $_G['cache']['historyposts'] ? explode("\t", $_G['cache']['historyposts']) : array();

$threads = $posts = $todayposts = 0;

$todayposts = intval(C::t('forum_forum')->fetch_sum_todaypost());
$forumStatInfo = [
    'todayposts' => $todayposts,
];

$message = '';
foreach ($timeNum as $time => $num) {
    $redisKey = getStatKey($time);
    $redisVal = getStatData($redisKey);
    if (!isset($redisVal['todayposts'])) {
        setStatData($redisKey, $forumStatInfo, $time);
    } else {
        $alarm['num'][$time] = $forumStatInfo['todayposts'] - $redisVal['todayposts'];
        if ($alarm['num'][$time] >= $num) {
            $alarm['post'][$time] = true;
            $minute = $time / 60;
            $message .= "{$minute}分钟内发帖量：{$alarm['num'][$time]}，";
            break;
        }
    }
}

if ($message) {
    $dateline = time() - $time;
    $limit = $newThreadNum;
    $threadList = C::t('forum_thread')->fetch_all_by_dateline($dateline, 0, $limit);
    if ($threadList) {
        $message .= "\n最新主题为\n";
        $n = 1;
        foreach ($threadList as $thead) {
            $message .= "(" . $n++ . ")、" . $thead['subject'] . "\n";
        }
    }
    post_alarm($webhook, $message);
}
?>
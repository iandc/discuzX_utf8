<?php

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

$isBannedIp = 1; // 是否封禁ip 1：是，0：否
$isDelThread = 1;
$isDelPost = 1;

$debug = 0;

if ($debug) {
    $timeNum = [
        360 => 2,
        420 => 4,
        480 => 8,
        540 => 16,
        600 => 32,
    ];
    $ipNum = [
        360 => 2,
        420 => 4,
        480 => 8,
        540 => 16,
        600 => 32,
    ];
    $webhook = [
        'post' => 'https://oapi.dingtalk.com/robot/send?access_token=6ab852ed50a6369ded074a23050f513b6b44716d9c449e288cd31e797f1f194a',
        'del' => 'https://oapi.dingtalk.com/robot/send?access_token=6ab852ed50a6369ded074a23050f513b6b44716d9c449e288cd31e797f1f194a',
    ];
} else {
    $timeNum = [
        360 => 36,
        420 => 42,
        480 => 48,
        540 => 54,
        600 => 60,
    ];
    $ipNum = [
        360 => 45,
        420 => 55,
        480 => 65,
        540 => 75,
        600 => 85,
    ];
    $webhook = [
        'post' => 'https://oapi.dingtalk.com/robot/send?access_token=bca9fb29854748deb3d3670da600dbea220c4bcb1c996bd0d6d10e4432d8238e',
        'del' => 'https://oapi.dingtalk.com/robot/send?access_token=135cb5c0d388010d6f075881467d5db6f7625d33fda1aeb9464ecac046e98c2f',
    ];
}

$newThreadNum = 500;
$showThreadNum = 10;

$bannedTime = 365;
$bannedUser = 'rebot';

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
            $message .= "{$minute}分钟内发帖量：{$alarm['num'][$time]}";
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
        $post = $ipList = [];
        foreach ($threadList as $thread) {
            $post = C::t('forum_post')->fetch_visiblepost_by_tid($thread['posttableid'], $thread['tid']);
            $ipList[$post['useip']]++;
            if ($n < $showThreadNum + 1) {
                $message .= "(" . $n++ . ")、{$thread['subject']}，{$_G['siteurl']}thread-{$thread['tid']}-1-1.html\n";
            }
        }
    }
    post_alarm($webhook['post'], $message);

    $toDelIp = [];
    foreach ($ipList as $ip => $num) {
        if ($num > $ipNum[$time]) {
            $toDelIp[] = $ip;
        }
    }

    $ipMessage = $delPostMessage = '';
    $n = 1;
    $toDelIp = array_unique($toDelIp);
    $message = "被禁用ip为\n";
    foreach ($toDelIp as $ip) {
        $sip = explode('.', $ip);
        foreach (C::t('common_banned')->fetch_all_order_dateline() as $banned) {
            $exists = 0;
            for ($i = 1; $i <= 4; $i++) {
                if ($banned["ip$i"] == -1) {
                    $exists++;
                } elseif ($banned["ip$i"] == $sip[$i - 1]) {
                    $exists++;
                }
            }
        }
        if ($exists != 4) {
            $expiration = TIMESTAMP + $bannedTime * 86400;
            $sip[3] = -1;
            if ($isBannedIp) C::app()->session->update_by_ipban($sip[0], $sip[1], $sip[2], $sip[3]);
            $data = array(
                'ip1' => $sip[0],
                'ip2' => $sip[1],
                'ip3' => $sip[2],
                'ip4' => $sip[3],
                'admin' => $bannedUser,
                'dateline' => $_G['timestamp'],
                'expiration' => $expiration,
            );
            if ($isBannedIp) C::t('common_banned')->insert($data);
            require_once libfile('function/cache');
            updatecache('ipbanned');
            $sip[3] = '*';
            $ipMessage .= $sip[0] . '.' . $sip[1] . '.' . $sip[2] . '.' . $sip[3] . " , ";

            //begin find post of the ip
            $sql = '';
            $posttableid = 0;
            $keywords = '';
            $fidaddarr = [];
            $uids = [];
            $starttime = $dateline;
            $endtime = time();
            $first = 1;
            $useip = trim($ip);
            if ($useip != '') {
                $useip = str_replace('*', '%', $useip);
            }
            $nocredit = 1;

            $pids = array();
            foreach (C::t('forum_post')->fetch_all_by_search($posttableid, null, $keywords, 0, $fidaddarr, $uids, null, $starttime, $endtime, $useip, $first, 0, 1000) as $post) {
                $pids[] = $post['pid'];
            }

            $count = count($pids);

            $pidsdelete = $tidsdelete = $pids_tids = [];
            $prune = ['forums' => [], 'thread' => []];

            foreach (C::t('forum_post')->fetch_all($posttableid, $pids, false) as $post) {
                $prune['forums'][$post['fid']] = $post['fid'];
                $pidsdelete[$post['fid']][$post['pid']] = $post['pid'];
                $pids_tids[$post['pid']] = $post['tid'];
                if ($post['first']) {
                    $tidsdelete[$post['pid']] = $post['tid'];
                } else {
                    @$prune['thread'][$post['tid']]++;
                }
            }

            if ($pidsdelete) {
                $tidCount = count($tidsdelete);
                $pidCount = array_sum($prune['thread']);
                $delPostMessage .= "自动删除主题帖：$tidCount, 回复帖：$pidCount";
                require_once libfile('function/post');
                require_once libfile('function/delete');
                $forums = C::t('forum_forum')->fetch_all($prune['forums']);
                foreach ($pidsdelete as $fid => $pids) {
                    foreach ($pids as $pid) {
                        if (!$tidsdelete[$pid]) {
                            $deletedposts = deletepost($pid, 'pid', !$nocredit, $posttableid, $forums[$fid]['recyclebin']);
                            updatemodlog($pids_tids[$pid], 'DLP');
                        } else {
                            $deletedthreads = deletethread(array($tidsdelete[$pid]), false, !$nocredit, $forums[$fid]['recyclebin']);
                            updatemodlog($tidsdelete[$pid], 'DEL');
                        }
                    }
                }

                if (count($prune['thread']) < 50) {
                    foreach ($prune['thread'] as $tid => $decrease) {
                        updatethreadcount($tid);
                    }
                } else {
                    $repliesarray = array();
                    foreach ($prune['thread'] as $tid => $decrease) {
                        $repliesarray[$decrease][] = $tid;
                    }
                    foreach ($repliesarray as $decrease => $tidarray) {
                        C::t('forum_thread')->increase($tidarray, array('replies' => -$decrease));
                    }
                }

                foreach (array_unique($prune['forums']) as $id) {
                    updateforumcount($id);
                }
            }
        }
    }

    if ($ipMessage) post_alarm($webhook['del'], $message . $ipMessage);
    if ($delPostMessage) post_alarm($webhook['del'], $delPostMessage);
}
?>
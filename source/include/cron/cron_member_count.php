<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cron_todaypost_daily.php 31920 2012-10-24 09:18:33Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$pertask = isset($_GET['pertask']) ? intval($_GET['pertask']) : 1000;
$current = isset($_GET['current']) && $_GET['current'] > 0 ? intval($_GET['current']) : 0;
$next = $current + $pertask;

$nextlink = "action=misc&operation=cron&run=20&current=$next&pertask=$pertask";
$processed = 0;

$blogs = 0;
$albums = 0;

$list = C::t('common_member')->range($current, $pertask);
foreach($list as $value) {
    $processed = 1;
    $blogs = C::t('home_blog')->count_by_uid($value['uid']);
    $albums = C::t('home_album')->count_by_uid($value['uid']);
    C::t('common_member_count')->update($value['uid'], array('blogs' => $blogs, 'albums' => $albums));
}

if($processed) {
    cpmsg("count_member_count_doing", $nextlink, 'loading');
} else {
    cpmsg('count_member_count_success', 'action=misc&operation=cron', 'succeed');
}

?>
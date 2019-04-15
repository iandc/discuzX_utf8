<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cron_todaypost_daily.php 31920 2012-10-24 09:18:33Z zhengqingpeng $
 */

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

$forums = C::t('forum_forum')->fetch_all_by_status(1);
$startTime = strtotime(date('Y-m-d', time()));
foreach ($forums as $forum) {
    if ($forum['type'] != 'group') {
        $tableid = 0;
        $todayposts = C::t('forum_post')->count_by_fid_dateline_invisible($tableid, $forum['fid'], $startTime, 0);
        C::t('forum_forum')->update($forum['fid'], array('todayposts' => $todayposts));
    }
}

?>
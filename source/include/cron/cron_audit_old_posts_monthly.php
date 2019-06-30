<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cron_announcement_daily.php 25786 2011-11-22 06:17:25Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$dbIndex = 8;

$sqlTemp = 'INSERT INTO pre_forum_post_moderate (id, status, dateline) SELECT p.pid, 0, p.dateline FROM pre_forum_post_%d p WHERE p.invisible=-2  AND NOT exists (SELECT * FROM pre_forum_post_moderate where id=p.pid)';
while($dbIndex--) {
    $sql = sprintf($sqlTemp, $dbIndex);
    DB::query($sql, 'SILENT');
}

?>
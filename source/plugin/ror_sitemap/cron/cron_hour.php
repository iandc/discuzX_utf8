<?php

//cronname:ror_sitemap
//week:
//day:
//hour:
//minute:00

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

//您的计划任务脚本内容
$plugin_name = 'ror_sitemap';

$url = $_G['siteurl'].'plugin.php?id='.$plugin_name;

dfsockopen($url);
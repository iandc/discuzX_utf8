<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$view = $_GET['type'];
if(!in_array($view, ['reward'])) {
	$view = 'reward';
}
$lang = lang('forum/template');
$navtitle = $lang['guide'].'-'.$lang['guide_'.$view];
$perpage = 50;
$start = $perpage * ($_G['page'] - 1);
$data = array();

include template('forum/block_'.$view);

?>
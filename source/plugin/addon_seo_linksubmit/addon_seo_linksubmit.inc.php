<?php

/**
 * Copyright 2001-2099 1314 学习.网.
 * This is NOT a freeware, use is subject to license terms
 * $Id: addon_seo_linksubmit.inc.php 2055 2019-11-20 20:15:32
 * 应用售后问题：http://www.1314study.com/services.php?mod=issue（备用 http://t.cn/RU4FEnD）
 * 应用售前咨询：QQ 153.26.940
 * 应用定制开发：QQ 64.330.67.97
 * 本插件为 1314学习网（www.1314study.com） 独立开发的原创插件, 依法拥有版权。
 * 未经允许不得公开出售、发布、使用、修改，如需购买请联系我们获得授权。
 */

if(!defined('IN_DISCUZ')) {
exit('Access Denied');
}
include_once libfile('function/core', 'plugin/addon_seo_linksubmit/source');
$tedxhmh5 = md5('2019121816GY38NAINIn');
$sz1hnb7p = 'a555a20f1c8b860f37da71305d431f54';
if($tedxhmh5 != $sz1hnb7p){die();}
$splugin_setting = $_G['cache']['plugin']['addon_seo_linksubmit'];# 正版： http://t.cn/hbdjxV
$splugin_lang = lang('plugin/addon_seo_linksubmit');
$study_gids = unserialize($splugin_setting['study_gids']);
$tid = intval($_GET['tid']);
$thread = DB::fetch_first("SELECT * FROM ".DB::table('forum_thread')." WHERE tid='$tid'");
//$threadtableids = !empty($_G['cache']['threadtableids']) ? $_G['cache']['threadtableids'] : array();
//$tableid = $_GET['archiveid'] && in_array($_GET['archiveid'], $threadtableids) ? intval($_GET['archiveid']) : 0;
if(empty($thread)){
showmessage($splugin_lang['slang_008']);//www_discuz_1314study_com
}elseif(!empty($thread['linksubmit'])){
showmessage($splugin_lang['slang_006']);
}elseif(!in_array($_G['groupid'], $study_gids)){
showmessage('&#x975E;&#x6CD5;&#x64CD;&#x4F5C;');
$rczdy1tx = "1314学习網";
}
if(submitcheck('submit')){
$thread['original'] = 0;//$_POST['original'] ? 1 : 0;
$status = addon_seo_linksubmit_baidu($thread);
if($status > 0){
showmessage($splugin_lang['slang_003'], 'forum.php?mod=viewthread&tid='.$tid);
}elseif($status == -1){
showmessage($splugin_lang['slang_004'], 'forum.php?mod=viewthread&tid='.$tid);/*本插件为 1314 学 习 网（www . 1314Study . com） 独立开发的原创插件, 依法拥有版权*/
}elseif($status == -2){
showmessage($splugin_lang['slang_005'], 'forum.php?mod=viewthread&tid='.$tid);
}elseif($status == -3){
showmessage($splugin_lang['slang_006'], 'forum.php?mod=viewthread&tid='.$tid);/*1314学习网*/
}elseif($status === 0){
showmessage($splugin_lang['slang_007'], 'forum.php?mod=viewthread&tid='.$tid);
}
showmessage('&#x64CD;&#x4F5C;&#x6210;&#x529F;', 'forum.php?mod=viewthread&tid='.$tid);
}	else{
$thread['dateline'] = dgmdate($thread['dateline']);
include template('addon_seo_linksubmit:push');
}


//Copyright 2001-2099 .1314.学习网.
//This is NOT a freeware, use is subject to license terms
//$Id: addon_seo_linksubmit.inc.php 2531 2019-11-20 12:15:32
//应用售后问题：http://www.1314study.com/services.php?mod=issue （备用 http://t.cn/EUPqQW1）
//应用售前咨询：QQ 15.3269.40
//应用定制开发：QQ 643.306.797
//本插件为 131.4学习网（www.1314Study.com） 独立开发的原创插件, 依法拥有版权。
//未经允许不得公开出售、发布、使用、修改，如需购买请联系我们获得授权。
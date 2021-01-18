<?php

/**
 * Copyright 2001-2099 1314 学习.网.
 * This is NOT a freeware, use is subject to license terms
 * $Id: admin_manage.inc.php 4803 2019-11-20 20:15:32
 * 应用售后问题：http://www.1314study.com/services.php?mod=issue（备用 http://t.cn/RU4FEnD）
 * 应用售前咨询：QQ 153.26.940
 * 应用定制开发：QQ 64.330.67.97
 * 本插件为 1314学习网（www.1314study.com） 独立开发的原创插件, 依法拥有版权。
 * 未经允许不得公开出售、发布、使用、修改，如需购买请联系我们获得授权。
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
exit('147A4197-B868-4F16-F483-1365728E4929');
}
define('STUDY_MANAGE_URL', 'plugins&operation=config&do='.$pluginid.'&identifier='.dhtmlspecialchars($_GET['identifier']).'&pmod=admin_manage');
require_once libfile('function/var', 'plugin/addon_seo_linksubmit/source');//  版权：1314 学 习 网，未经允许不得公开出售、发布、使用、修改，如需购买请联系我们获得授权
require_once libfile('class/admin', 'plugin/addon_seo_linksubmit/source');                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   $_statInfo = array();$_statInfo['pluginName'] = $plugin['identifier'];$_statInfo['pluginVersion'] = $plugin['version'];$_statInfo['bbsVersion'] = DISCUZ_VERSION;$_statInfo['bbsRelease'] = DISCUZ_RELEASE;$_statInfo['timestamp'] = TIMESTAMP;$_statInfo['bbsUrl'] = $_G['siteurl'];$_statInfo['SiteUrl'] = 'http://bbs.eetop.cn/';$_statInfo['ClientUrl'] = 'http://bbs.eetop.cn/';$_statInfo['SiteID'] = '147A4197-B868-4F16-F483-1365728E4929';$_statInfo['bbsAdminEMail'] = $_G['setting']['adminemail'];$_statInfo['genuine'] = splugin_genuine($plugin['identifier']);
loadcache('plugin');
$splugin_setting = $_G['cache']['plugin']['addon_seo_linksubmit'];
$splugin_lang = lang('plugin/addon_seo_linksubmit');
$type1314 = in_array($_GET['type1314'], array('linksubmit', 'push')) ? $_GET['type1314'] : 'linksubmit';
$splugin_setting['0'] = array('0' => '2019121816GY38NAINIn', '1' => '63033','2' => '1574253291', '3' => 'http://bbs.eetop.cn/', '4' => 'http://bbs.eetop.cn/', '5' => '147A4197-B868-4F16-F483-1365728E4929', '6' => 'D57533AC-5F5B-3DB0-411A-C33C38DF3722', '7' => 'aec7c70545de669681342518f78362e2');
echo '<link href="./source/plugin/addon_seo_linksubmit/images/manage.css?'.VERHASH.'" rel="stylesheet" type="text/css" />';
addon_seo_linksubmit_admin::subtitle(array(
	array('&#x63A8;&#x9001;&#x5217;&#x8868;', 'linksubmit'),
	array('&#x624B;&#x52A8;&#x63A8;&#x9001;', 'push'),
),$type1314);

require_once libfile('admin/manage_'.$type1314, 'plugin/addon_seo_linksubmit/source');

//Copyright 2001-2099 .1314.学习网.
//This is NOT a freeware, use is subject to license terms
//$Id: admin_manage.inc.php 5271 2019-11-20 12:15:32
//应用售后问题：http://www.1314study.com/services.php?mod=issue （备用 http://t.cn/EUPqQW1）
//应用售前咨询：QQ 15.3269.40
//应用定制开发：QQ 643.306.797
//本插件为 131.4学习网（www.1314Study.com） 独立开发的原创插件, 依法拥有版权。
//未经允许不得公开出售、发布、使用、修改，如需购买请联系我们获得授权。
<?php

/**
 * Copyright 2001-2099 1314 学习.网.
 * This is NOT a freeware, use is subject to license terms
 * $Id: changelog.inc.php 922 2019-11-20 20:15:32
 * 应用售后问题：http://www.1314study.com/services.php?mod=issue（备用 http://t.cn/RU4FEnD）
 * 应用售前咨询：QQ 153.26.940
 * 应用定制开发：QQ 64.330.67.97
 * 本插件为 1314学习网（www.1314study.com） 独立开发的原创插件, 依法拥有版权。
 * 未经允许不得公开出售、发布、使用、修改，如需购买请联系我们获得授权。
 */
/*
 * This is NOT a freeware, use is subject to license terms
 * From www.1314study.com ver 2.0
 */
if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
exit('Access Denied');
}
define('STUDYADDONS_FEEDBACK_URL', 'http'.($_G['isHTTPS'] ? 's' : '').'://addon.1314study.com/changelog/index.php');
require_once ('pluginvar.func.php');//  本插件为 1314 学 习 网（www . 1314Study . com） 独立开发的原创插件, 依法拥有版权
require_once DISCUZ_ROOT.'./source/discuz_version.php';
$data = 'pid='.$plugin['identifier'].'&siteurl='.rawurlencode($_G['siteurl']).'&sitever='.DISCUZ_VERSION.'/'.DISCUZ_RELEASE.'&sitecharset='.CHARSET.'&pversion='.rawurlencode($plugin['version']);splugin_thinks($plugin['identifier'],0);
$param = 'data='.rawurlencode(base64_encode($data));
$param .= '&md5hash='.substr(md5($data.TIMESTAMP), 8, 8).'&timestamp='.TIMESTAMP;# 版权：www.1314Study.com
s_addon_stat($plugin,'changelog');

//Copyright 2001-2099 .1314.学习网.
//This is NOT a freeware, use is subject to license terms
//$Id: changelog.inc.php 1386 2019-11-20 12:15:32
//应用售后问题：http://www.1314study.com/services.php?mod=issue （备用 http://t.cn/EUPqQW1）
//应用售前咨询：QQ 15.3269.40
//应用定制开发：QQ 643.306.797
//本插件为 131.4学习网（www.1314Study.com） 独立开发的原创插件, 依法拥有版权。
//未经允许不得公开出售、发布、使用、修改，如需购买请联系我们获得授权。
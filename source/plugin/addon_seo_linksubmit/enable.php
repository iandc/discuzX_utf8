<?php

/**
 * Copyright 2001-2099 1314 学习.网.
 * This is NOT a freeware, use is subject to license terms
 * $Id: enable.php 686 2019-11-20 20:15:32
 * 应用售后问题：http://www.1314study.com/services.php?mod=issue（备用 http://t.cn/RU4FEnD）
 * 应用售前咨询：QQ 153.26.940
 * 应用定制开发：QQ 64.330.67.97
 * 本插件为 1314学习网（www.1314study.com） 独立开发的原创插件, 依法拥有版权。
 * 未经允许不得公开出售、发布、使用、修改，如需购买请联系我们获得授权。
 */

if(!defined('IN_ADMINCP')) {
exit('Access Denied');
}
$addonid = $plugin['identifier'].'.plugin';#From Www.1314Study.com
$array = cloudaddons_getmd5($addonid);#From Www.1314Study.com
if(cloudaddons_open('&mod=app&ac=validator&addonid='.$addonid.($array !== false ? '&rid='.$array['RevisionID'].'&sn='.$array['SN'].'&rd='.$array['RevisionDateline'] : '')) === '0') {
$available = $operation == 'enable' ? 0 : 1;#http://t.cn/hbdjxV
C::t('common_plugin')->update($_GET['pluginid'], array('available' => $available));//1_3.1.4.学.习.网
cpmsg('plugins_'.$operation.'_succeed', 'action=plugins'.(!empty($_GET['system']) ? '&system=1' : ''), 'succeed');//http://t.cn/hbdjxV
}

//Copyright 2001-2099 .1314.学习网.
//This is NOT a freeware, use is subject to license terms
//$Id: enable.php 1143 2019-11-20 12:15:32
//应用售后问题：http://www.1314study.com/services.php?mod=issue （备用 http://t.cn/EUPqQW1）
//应用售前咨询：QQ 15.3269.40
//应用定制开发：QQ 643.306.797
//本插件为 131.4学习网（www.1314Study.com） 独立开发的原创插件, 依法拥有版权。
//未经允许不得公开出售、发布、使用、修改，如需购买请联系我们获得授权。
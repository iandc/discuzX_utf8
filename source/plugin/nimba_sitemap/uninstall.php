<?php
/*
 * 主页：https://addon.dismall.com/?@1552.developer
 * 人工智能实验室：Discuz!应用中心十大优秀开发者！
 * 插件定制 联系QQ594941227
 * From www.ailab.cn
 */
 
if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}
$sum=2;
while(file_exists(DISCUZ_ROOT.'/sitemap_'.$sum.'.xml')){
	unlink(DISCUZ_ROOT.'/sitemap_'.$sum.'.xml');
	$sum++;
}
$sum=2;
while(file_exists(DISCUZ_ROOT.'/data/sitemap_'.$sum.'.xml')){
	unlink(DISCUZ_ROOT.'/data/sitemap_'.$sum.'.xml');
	$sum++;
}
if(file_exists(DISCUZ_ROOT.'/sitemap.xml')){
	unlink(DISCUZ_ROOT.'/sitemap.xml');
}
if(file_exists(DISCUZ_ROOT.'/data/sitemap.xml')){
	unlink(DISCUZ_ROOT.'/data/sitemap.xml');
}
if(file_exists(DISCUZ_ROOT.'./data/sysdata/cache_nimba_sitemap_log.php')) @unlink(DISCUZ_ROOT.'./data/sysdata/cache_nimba_sitemap_log.php');
$identifier = 'nimba_sitemap';
if(!function_exists('cloudaddons_deltree')) require libfile('function/cloudaddons');
cloudaddons_deltree(DISCUZ_ROOT .'./source/plugin/'.$identifier.'/');
$finish = TRUE;

?>
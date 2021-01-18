<?php
/*
 * Install Uninstall Upgrade AutoStat System Code 2019121816GY38NAINIn
 * This is NOT a freeware, use is subject to license terms
 * From www.1314study.com
 */
if(!defined('IN_ADMINCP')) {
	exit('Access Denied');
}

empty($pluginarray['plugin']) && $pluginarray['plugin'] = $plugin;

require_once DISCUZ_ROOT.'./source/discuz_version.php';
require_once DISCUZ_ROOT.'./source/plugin/'.$pluginarray['plugin']['identifier'].'/installlang.lang.php';
$request_url = str_replace('&step='.$_GET['step'],'',$_SERVER['QUERY_STRING']);

//3.1以后版本直接跳到删除数据库
if(str_replace('X', '', DISCUZ_VERSION) >= 3.1){
	$_GET['step'] = 'sql';
	$_GET['deletesql'] = '2019121816GY38NAINIn';
}
$identifier = $identifier ? $identifier : $pluginarray['plugin']['identifier'];

$available = dfsockopen('http://addon.1314study.com/api/available.php?siteurl='.rawurlencode($_G['siteurl']).'&identifier='.$identifier, 0, '', '', false, '', 5);
if($available == 'succeed'){
	$available = 1;
}else{
	$available = 0;
}

$sql = <<<EOF
DROP TABLE IF EXISTS `pre_addon_seo_linksubmit`;
EOF;
runquery($sql);

$columns = array();
$query = DB::query("SHOW COLUMNS FROM ".DB::table('forum_thread'));
while($temp = DB::fetch($query)) {
	$columns[] = $temp['Field'];
}

if(in_array('linksubmit', $columns)){
	DB::query("ALTER TABLE ".DB::table('forum_thread')." DROP `linksubmit`");
}

$settings = C::t('common_setting')->fetch_all(array('threadtableids'), true);
$threadtableids = $settings['threadtableids'] ? $settings['threadtableids'] : array();
foreach($threadtableids as $tableid) {
	if(!$tableid) {
		continue;
	}
	$tablename = "forum_thread_$tableid";
	$columns = array();
	$query = DB::query("SHOW COLUMNS FROM ".DB::table($tablename));
	while($temp = DB::fetch($query)) {
		$columns[] = $temp['Field'];
	}
	
	if(in_array('linksubmit', $columns)){
		DB::query("ALTER TABLE ".DB::table($tablename)." DROP `linksubmit`");
	}
}

$_statInfo = array();
$_statInfo['pluginName'] = $pluginarray['plugin']['identifier'];
$_statInfo['pluginVersion'] = $pluginarray['plugin']['version'];
$_statInfo['bbsVersion'] = DISCUZ_VERSION;
$_statInfo['bbsRelease'] = DISCUZ_RELEASE;
$_statInfo['timestamp'] = TIMESTAMP;
$_statInfo['bbsUrl'] = $_G['siteurl'];
$_statInfo['SiteUrl'] = 'http://bbs.eetop.cn/';
$_statInfo['ClientUrl'] = 'http://bbs.eetop.cn/';
$_statInfo['SiteID'] = '147A4197-B868-4F16-F483-1365728E4929';
$_statInfo['bbsAdminEMail'] = $_G['setting']['adminemail'];
$_statInfo['action'] = 'uninstall';
$_statInfo['genuine'] = splugin_genuine($pluginarray['plugin']['identifier']);
$_statInfo = base64_encode(serialize($_statInfo));
$_md5Check = md5($_statInfo);
$StatUrl = 'http://addon.1314study.com/stat.php';
$_StatUrl = $StatUrl.'?info='.$_statInfo.'&md5check='.$_md5Check;
dfsockopen($_StatUrl, 0, '', '', false, '', 5);
$_statInfo = array();
$_statInfo['pluginName'] = $pluginarray['plugin']['identifier'];
$_statInfo['bbsVersion'] = DISCUZ_VERSION;
$_statInfo['bbsUrl'] = $_G['siteurl'];
$_statInfo['action'] = 'uninstall';
$_statInfo['nextUrl'] = ADMINSCRIPT.'?'.$request_url;
$_statInfo = base64_encode(serialize($_statInfo));
$_md5Check = md5($_statInfo);
$_StatUrl = 'http'.($_G['isHTTPS'] ? 's' : '').'://addon.1314study.com/api/outer_addon.php?type=js&info='.$_statInfo.'&md5check='.$_md5Check;
if(preg_match("/^[a-z]+[a-z0-9_]*$/i", $identifier)){
	if(function_exists('cron_delete')) {
		cron_delete($identifier);
	}
	loadcache('pluginlanguage_install', 1);
	if(!empty($_G['cache']['pluginlanguage_install']) && isset($_G['cache']['pluginlanguage_install'][$identifier])) {
		unset($_G['cache']['pluginlanguage_install'][$identifier]);
		savecache('pluginlanguage_install', $_G['cache']['pluginlanguage_install']);
	}
	cloudaddons_uninstall($identifier.'.plugin', DISCUZ_ROOT.'./source/plugin/'.$identifier);
}
C::t('common_syscache')->delete('scache_'.$pluginarray['plugin']['identifier']);

cpmsg('plugins_delete_succeed', $_StatUrl, 'succeed');
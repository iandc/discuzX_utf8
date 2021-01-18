<?php

/**
 *      $author: 乘凉 $
 */

if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$setconfig = $_G['cache']['plugin'][CURMODULE];
$setconfig['compress_replace'] = $setconfig['compress_replace'] ? 'false' : 'true';
$setconfig['nocompress_forums'] = (array)unserialize($setconfig['nocompress_forums']);
if(!$setconfig['qrcode_upload']) {
	dexit();
}

if($_GET['operation'] == 'forum') {
	if($_GET['qrcode']){
		require_once DISCUZ_ROOT . './source/plugin/'.CURMODULE.'/lib/phpqrcode.php';
		$_G['fid'] = intval($_GET['fid']);
		$hash = authcode("$_G[uid]\t$_G[fid]\t$_G[timestamp]", 'ENCODE', md5(substr(md5($_G['config']['security']['authkey']), 0, 16)));
		$hash = urlencode($hash);
		$qrcodeurl = $_G['siteurl'].'plugin.php?id=h5upload:qrupload&operation=forum&hash='.$hash;
		QRcode::png($qrcodeurl, false, 'L', 10, 1); 
		exit();
	}
	$_GET['hash'] = empty($_GET['hash']) ? '' : $_GET['hash'];
	if(!$_GET['hash']) {
		exit();
	}
	list($uid, $fid, $timestamp) = explode("\t", authcode($_GET['hash'], 'DECODE', md5(substr(md5($_G['config']['security']['authkey']), 0, 16))));
	$uid = intval($uid);
	$fid = intval($fid);
	$timestamp = intval($timestamp);
	if(!$uid || !$fid || !$timestamp) {
		exit();
	}
	if($_G['timestamp'] - $timestamp > 3600) {
		showmessage(lang('plugin/'.CURMODULE, 'qrupload_overdue'));
	}

	if($uid) {
		$_G['member'] = getuserbyuid($uid);
	}
	$_G['groupid'] = $_G['member']['groupid'];
	loadcache('usergroup_'.$_G['member']['groupid']);
	$_G['group'] = $_G['cache']['usergroup_'.$_G['member']['groupid']];

	require_once libfile('function/upload');
	$swfconfig = getuploadconfig($uid, $fid);
	$imgexts = str_replace(array(';', '*.'), array(', ', ''), $swfconfig['imageexts']['ext']);
	$swfconfig['imageexts']['mime'] = get_mimeTypes($swfconfig['imageexts']['ext']);
	$swfconfig['imageexts']['ext'] = str_replace(array("*.", ";"), array("", ","), $swfconfig['imageexts']['ext']);
	$swfconfig["max"] = $swfconfig["max"]*1024;
	$maxattachsize_mb = $_G['group']['maxattachsize'] / 1048576 >= 1 ? round(($_G['group']['maxattachsize'] / 1048576), 1).'MB' : round(($_G['group']['maxattachsize'] / 1024)).'KB';
	if(in_array($fid, $setconfig['nocompress_forums'])){
		$setconfig['compress_open'] = 0;
	}
	//开启微信上传
	$wxconfig = array();
	if($setconfig['wechat_upload'] && strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false){
		if($setconfig['wechat_appid'] && $setconfig['wechat_appsecret']){
			require_once DISCUZ_ROOT . './source/plugin/h5upload/lib/wechat.class.php';
			$wechat_client = new h5upload_wechat($setconfig['wechat_appid'], $setconfig['wechat_appsecret']);
			if(!$setconfig['wechat_cache']){
				$wechat_client->setNoCache(array("AccessToken", "JsApiTicket"));
			}
			$jsapiTicket = $wechat_client->getJsApiTicket();
			$protocol = $this->_get_http_type() ? "https://" : "http://";
			$url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
			$timestamp = time();
			$noncestr = "";
			$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
			for ($i = 0; $i < 16; $i++) {
				$noncestr .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
			}
			$string = "jsapi_ticket=$jsapiTicket&noncestr=$noncestr&timestamp=$timestamp&url=$url";
			$signature = sha1($string);
			$wxconfig = array(
				"appid"     => $setconfig['wechat_appid'],
				"noncestr"  => $noncestr,
				"timestamp" => $timestamp,
				"url"       => $url,
				"signature" => $signature,
			);
		}
	}
	if(defined('IN_MOBILE')) {
		include template(CURMODULE.':qrupload_post');
	}else{
		dheader('location: forum.php?mod=post&action=newthread&fid='.$fid);
	}

} elseif($_GET['operation'] == 'portal') {
	if($_GET['qrcode']){
		require_once DISCUZ_ROOT . './source/plugin/'.CURMODULE.'/lib/phpqrcode.php';
		$aid = intval($_GET['aid']);
		$_G['catid'] = intval($_GET['catid']);
		$hash = authcode("$_G[uid]\t$aid\t$_G[catid]\t$_G[timestamp]", 'ENCODE', md5(substr(md5($_G['config']['security']['authkey']), 0, 16)));
		$hash = urlencode($hash);
		$qrcodeurl = $_G['siteurl'].'plugin.php?id=h5upload:qrupload&operation=portal&hash='.$hash;
		QRcode::png($qrcodeurl, false, 'L', 10, 1); 
		exit();
	}
	$_GET['hash'] = empty($_GET['hash']) ? '' : $_GET['hash'];
	if(!$_GET['hash']) {
		exit();
	}
	list($uid, $aid, $catid, $timestamp) = explode("\t", authcode($_GET['hash'], 'DECODE', md5(substr(md5($_G['config']['security']['authkey']), 0, 16))));
	$uid = intval($uid);
	$aid = intval($aid);
	$catid = intval($catid);
	$timestamp = intval($timestamp);
	if(!$uid || !$timestamp) {
		exit();
	}
	if($_G['timestamp'] - $timestamp > 3600) {
		showmessage(lang('plugin/'.CURMODULE, 'qrupload_overdue'));
	}

	if($uid) {
		$_G['member'] = getuserbyuid($uid);
	}
	$_G['groupid'] = $_G['member']['groupid'];
	loadcache('usergroup_'.$_G['member']['groupid']);
	$_G['group'] = $_G['cache']['usergroup_'.$_G['member']['groupid']];

	require_once libfile('function/upload');
	$swfconfig = getuploadconfig($uid);
	$imgexts = str_replace(array(';', '*.'), array(', ', ''), $swfconfig['imageexts']['ext']);
	$swfconfig['imageexts']['mime'] = get_mimeTypes($swfconfig['imageexts']['ext']);
	$swfconfig['imageexts']['ext'] = str_replace(array("*.", ";"), array("", ","), $swfconfig['imageexts']['ext']);
	$swfconfig["max"] = $swfconfig["max"]*1024;
	$maxattachsize_mb = $_G['group']['maxattachsize'] / 1048576 >= 1 ? round(($_G['group']['maxattachsize'] / 1048576), 1).'MB' : round(($_G['group']['maxattachsize'] / 1024)).'KB';

	//开启微信上传
	$wxconfig = array();
	if($setconfig['wechat_upload'] && strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false){
		if($setconfig['wechat_appid'] && $setconfig['wechat_appsecret']){
			require_once DISCUZ_ROOT . './source/plugin/h5upload/lib/wechat.class.php';
			$wechat_client = new h5upload_wechat($setconfig['wechat_appid'], $setconfig['wechat_appsecret']);
			if(!$setconfig['wechat_cache']){
				$wechat_client->setNoCache(array("AccessToken", "JsApiTicket"));
			}
			$jsapiTicket = $wechat_client->getJsApiTicket();
			$protocol = $this->_get_http_type() ? "https://" : "http://";
			$url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
			$timestamp = time();
			$noncestr = "";
			$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
			for ($i = 0; $i < 16; $i++) {
				$noncestr .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
			}
			$string = "jsapi_ticket=$jsapiTicket&noncestr=$noncestr&timestamp=$timestamp&url=$url";
			$signature = sha1($string);
			$wxconfig = array(
				"appid"     => $setconfig['wechat_appid'],
				"noncestr"  => $noncestr,
				"timestamp" => $timestamp,
				"url"       => $url,
				"signature" => $signature,
			);
		}
	}
	if(defined('IN_MOBILE')) {
		include template(CURMODULE.':qrupload_portal');
	}else{
		dheader('location: portal.php?mod=portalcp&ac=article&catid='.$catid);
	}
} elseif($_GET['operation'] == 'portalpic') {
	$aid = intval($_GET['aid']);
	$posttime = intval($_GET['posttime']);
	$wherearr = array();
	$wherearr[] = "isimage='1'";
	if($aid) {
		$wherearr[] = "aid='$aid'";
	}
	if($posttime) {
		$wherearr[] = "dateline>'$posttime'";
	}
	$wheresql = empty($wherearr) ? '' : implode(' AND ', $wherearr);
	$imgattachs = DB::fetch_all('SELECT * FROM %t '.($wheresql ? ' WHERE '.$wheresql : '').' ORDER BY attachid DESC', array('portal_attachment'), 'attachid');
	helper_output::json(array('aids' => array_keys($imgattachs)));
} elseif($_GET['operation'] == 'forumpic') {
	require_once libfile('function/post');
	$attachlist = getattach($_GET['pid'], intval($_GET['posttime']), $_GET['aids']);
	$imagelist = $attachlist['imgattachs']['unused'];
	$aids = array();
	foreach($imagelist as $image){
		$aids[] = $image['aid'];
	}
	helper_output::json(array('aids' => $aids));
}

function get_http_type() {
	if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
		return true;
	} elseif ($_SERVER['SERVER_PORT'] == 443) {
		return true;
	} elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
		return true;
	} elseif (!empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off') {
		return true;
	}
	return false;
}

function get_mimeTypes($ext) {
	$mime = '';
	if($ext != '*.*'){
		include DISCUZ_ROOT.'./source/plugin/h5upload/lib/mime_type.php';
		$mimeStr = str_replace(array("*."), array(""), $ext);
		$mimeArr = explode(";",$mimeStr);
		$getmimeArr = array();
		foreach($mimeArr as $value) {
			$getmimeArr[] = $mimeType[$value] ? $mimeType[$value] : '.'.$value;
		}
		$getmimeArr = array_unique($getmimeArr);
		$mime = implode(",",$getmimeArr); 
		//$mime = str_replace(array("*.", ";"), array(".", ","), $ext);
	}
	return $mime;
}
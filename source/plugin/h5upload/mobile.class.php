<?php

/**
 *      $author: 乘凉 $
 */

if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class mobileplugin_h5upload {

	public static $identifier = 'h5upload';

	function __construct() {
		global $_G;
		$setconfig = $_G['cache']['plugin'][self::$identifier];
		if($setconfig['upload_storage'] == 'cl_qiniuyun' && !file_exists(DISCUZ_ROOT.'./source/plugin/cl_qiniuyun/upload.inc.php')){
			$setconfig['upload_storage'] = '';
		}
		if($setconfig['upload_storage'] == 'cl_aliyun' && !file_exists(DISCUZ_ROOT.'./source/plugin/cl_aliyun/upload.inc.php')){
			$setconfig['upload_storage'] = '';
		}
		$setconfig['compress_replace'] = $setconfig['compress_replace'] ? 'false' : 'true';
		$setconfig['nocompress_forums'] = (array)unserialize($setconfig['nocompress_forums']);
		$this->setconfig = $setconfig;
	}

	function global_footer_mobile() {
		global $_G, $swfconfig, $aid, $catid, $imgattachs;
		$setconfig = $this->setconfig;
		if(!$setconfig['mobile_closed'] && ((CURSCRIPT == 'forum' && CURMODULE == 'post') || (CURSCRIPT == 'portal' && CURMODULE == 'portalcp'))){
			$swfconfig[imageexts][mime] = $this->_get_mimeTypes($swfconfig[imageexts][ext]);
			$swfconfig[imageexts][ext] = str_replace(array("*.", ";"), array("", ","), $swfconfig[imageexts][ext]);
			$swfconfig["max"] = $swfconfig["max"]*1024;
			//开启微信上传
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
					if(CURSCRIPT == 'forum'){
						if(in_array($_G['fid'], $setconfig['nocompress_forums'])){
							$setconfig['compress_open'] = 0;
						}
						include template(self::$identifier.':wechat_post');
					}elseif(CURSCRIPT == 'portal'){
						include template(self::$identifier.':wechat_portal');
					}
				}else{
					if(CURSCRIPT == 'forum'){
						if(in_array($_G['fid'], $setconfig['nocompress_forums'])){
							$setconfig['compress_open'] = 0;
						}
						include template(self::$identifier.':post_upload');
					}elseif(CURSCRIPT == 'portal'){
						include template(self::$identifier.':portal_upload');
					}
				}
			}else{
				if(CURSCRIPT == 'forum'){
					if(in_array($_G['fid'], $setconfig['nocompress_forums'])){
						$setconfig['compress_open'] = 0;
					}
					include template(self::$identifier.':post_upload');
				}elseif(CURSCRIPT == 'portal'){
					include template(self::$identifier.':portal_upload');
				}
			}
		}
		return $return;
	}

	function _get_http_type() {
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

	function _get_mimeTypes($ext) {
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
}

class mobileplugin_h5upload_forum extends mobileplugin_h5upload {

	function ajax() {
		global $_G;
		$setconfig = $this->setconfig;
		if($_GET['action'] == 'deleteattach') {
			$uid = intval($_POST['uid']);
			if($uid && $_POST['hash'] == md5(substr(md5($_G['config']['security']['authkey']), 8).$uid)) {
				$_G['uid'] = $uid;
			}
		}
	}

	function viewthread_top_mobile_output() {
		global $_G,$postlist;
		$setconfig = $this->setconfig;
		if($setconfig['attach_order'] == 1){
			foreach($postlist as $key => $post) {
				if(!empty($post['imagelist']) && is_array($post['imagelist'])) {
					natsort($post['imagelist']);
				}
				if(!empty($post['attachlist']) && is_array($post['attachlist'])) {
					natsort($post['attachlist']);
				}
				$postlist[$key] = $post;
			}
		}
		if($setconfig['attach_order'] == 2){
			foreach($postlist as $key => $post) {
				if(!empty($post['imagelist']) && is_array($post['imagelist'])) {
					$imagelist = array();
					foreach($post['imagelist'] as $aid) {
						if(!empty($post['attachments'][$aid])) {
							$imagelist[$aid] = $post['attachments'][$aid]['filename'];
						}
					}
					if($imagelist){
						natsort($imagelist);
						$post['imagelist'] = array_keys($imagelist);
					}
				}
				if(!empty($post['attachlist']) && is_array($post['attachlist'])) {
					$attachlist = array();
					foreach($post['attachlist'] as $aid) {
						if(!empty($post['attachments'][$aid])) {
							$attachlist[$aid] = $post['attachments'][$aid]['filename'];
						}
					}
					if($attachlist){
						natsort($attachlist);
						$post['attachlist'] = array_keys($attachlist);
					}
				}
				$postlist[$key] = $post;
			}
		}
	}

}

class mobileplugin_h5upload_misc extends mobileplugin_h5upload {

	function swfupload() {
		global $_G;
		$setconfig = $this->setconfig;
		//if($setconfig['ban_muma']){
			require DISCUZ_ROOT . './source/plugin/h5upload/lib/discuz_upload.php';
		//}
	}

}

?>
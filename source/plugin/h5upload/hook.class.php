<?php

/**
 *      $author: ³ËÁ¹ $
 */

if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class plugin_h5upload {

	public static $identifier = 'h5upload';

	function __construct() {
		global $_G;
		$setconfig = $_G['cache']['plugin'][self::$identifier];
		if($setconfig['white_domain']){
			$websiteArr = array();
			foreach(explode("\n", $setconfig['white_domain']) as $key => $option) {
				$option = trim($option);
				if($option){
					$websiteArr[] = "'".$option."'";
				}
			}
			$setconfig['white_domain'] = implode(",",$websiteArr);
		}
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
			$mime = str_replace(array("*.", ";"), array(".", ","), $ext);
		}
        return $mime;
	}
}


class plugin_h5upload_forum extends plugin_h5upload {

	function forumdisplay_fastpost_upload_extend_output() {
		global $_G, $swfconfig;
		$setconfig = $this->setconfig;
		$swfconfig[imageexts][mime] = $this->_get_mimeTypes($swfconfig[imageexts][ext]);
		$swfconfig[imageexts][ext] = str_replace(array("*.", ";"), array("", ","), $swfconfig[imageexts][ext]);
		$swfconfig[attachexts][mime] = $this->_get_mimeTypes($swfconfig[attachexts][ext]);
		$swfconfig[attachexts][ext] = str_replace(array("*.", ";"), array("", ","), $swfconfig[attachexts][ext]);
		$swfconfig["max"] = $swfconfig["max"]*1024;
		if(in_array($_G['fid'], $setconfig['nocompress_forums'])){
			$setconfig['compress_open'] = 0;
		}
		include template(self::$identifier.':fastpost_upload');
		if(!empty($_G['setting']['pluginhooks']['forumdisplay_fastpost_upload_extend'])){
			$_G['setting']['pluginhooks']['forumdisplay_fastpost_upload_extend'] = $return;
		}else{
			return $return;
		}
	}

	function viewthread_top_output() {
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

	function viewthread_fastpost_upload_extend_output() {
		global $_G, $swfconfig;
		$setconfig = $this->setconfig;
		$swfconfig[imageexts][mime] = $this->_get_mimeTypes($swfconfig[imageexts][ext]);
		$swfconfig[imageexts][ext] = str_replace(array("*.", ";"), array("", ","), $swfconfig[imageexts][ext]);
		$swfconfig[attachexts][mime] = $this->_get_mimeTypes($swfconfig[attachexts][ext]);
		$swfconfig[attachexts][ext] = str_replace(array("*.", ";"), array("", ","), $swfconfig[attachexts][ext]);
		$swfconfig["max"] = $swfconfig["max"]*1024;
		if(in_array($_G['fid'], $setconfig['nocompress_forums'])){
			$setconfig['compress_open'] = 0;
		}
		include template(self::$identifier.':fastpost_upload');
		if(!empty($_G['setting']['pluginhooks']['viewthread_fastpost_upload_extend'])){
			$_G['setting']['pluginhooks']['viewthread_fastpost_upload_extend'] = $return;
		}else{
			return $return;
		}
	}

	function post_upload_extend_output() {
		global $_G, $swfconfig, $editorid, $allowpostimg, $special;
		if($special == 1){
			$_G['uploadjs'] = 1;
			if($_GET['action'] == 'newthread'){
				$_G['setting']['rewritestatus'] = '1';
				$_G['setting']['output']['str']['search']['addpolloption'] = 'addpolloption();';
				$_G['setting']['output']['str']['replace']['addpolloption'] = '';
			}
		}
		$setconfig = $this->setconfig;
		$swfconfig[imageexts][mime] = $this->_get_mimeTypes($swfconfig[imageexts][ext]);
		$swfconfig[imageexts][ext] = str_replace(array("*.", ";"), array("", ","), $swfconfig[imageexts][ext]);
		$swfconfig[attachexts][mime] = $this->_get_mimeTypes($swfconfig[attachexts][ext]);
		$swfconfig[attachexts][ext] = str_replace(array("*.", ";"), array("", ","), $swfconfig[attachexts][ext]);
		$swfconfig["max"] = $swfconfig["max"]*1024;
		if(in_array($_G['fid'], $setconfig['nocompress_forums'])){
			$setconfig['compress_open'] = 0;
		}
		include template(self::$identifier.':post_upload');
		if(!empty($_G['setting']['pluginhooks']['post_upload_extend'])){
			$_G['setting']['pluginhooks']['post_upload_extend'] = $return;
		}else{
			return $return;
		}
	}


	function post_poll_upload_extend_output() {
		global $_G, $swfconfig;
		$setconfig = $this->setconfig;
		$swfconfig["max"] = $swfconfig["max"]*1024;
		if(in_array($_G['fid'], $setconfig['nocompress_forums'])){
			$setconfig['compress_open'] = 0;
		}
		include template(self::$identifier.':post_poll_upload');
		if(!empty($_G['setting']['pluginhooks']['post_poll_upload_extend'])){
			$_G['setting']['pluginhooks']['post_poll_upload_extend'] = $return;
		}else{
			return $return;
		}
	}

	function post_sync_method() {
		global $_G;
		$setconfig = $this->setconfig;
		if((($_GET['action'] == 'newthread' && submitcheck('topicsubmit')) || ($_GET['action'] == 'reply' && submitcheck('replysubmit')) || ($_GET['action'] == 'edit' && submitcheck('editsubmit'))) && $setconfig['down_remote'] == 3 && $_G['group']['allowdownremoteimg']){
			//if($setconfig['ban_muma']){
				require DISCUZ_ROOT . './source/plugin/h5upload/lib/discuz_upload.php';
			//}
			require_once libfile('function/common', 'plugin/h5upload');
			preg_match_all("/\[img\]\s*([^\[\<\r\n]+?)\s*\[\/img\]|\[img=\d{1,4}[x|\,]\d{1,4}\]\s*([^\[\<\r\n]+?)\s*\[\/img\]/is", $_GET['message'], $image1, PREG_SET_ORDER);
			preg_match_all("/\<img.+src=('|\"|)?(.*)(\\1)([\s].*)?\>/ismUe", $_GET['message'], $image2, PREG_SET_ORDER);
			$temp = $aids = $existentimg = array();
			if(is_array($image1) && !empty($image1)) {
				foreach($image1 as $value) {
					$temp[] = array(
						'0' => $value[0],
						'1' => trim(!empty($value[1]) ? $value[1] : $value[2])
					);
				}
			}
			if(is_array($image2) && !empty($image2)) {
				foreach($image2 as $value) {
					$temp[] = array(
						'0' => $value[0],
						'1' => trim($value[2])
					);
				}
			}
			if(is_array($temp) && !empty($temp)) {
				$attachaids = array();
				foreach($temp as $value) {
					$imageurl = $value[1];
					$hash = md5($imageurl);
					if(strlen($imageurl)) {
						$imagereplace['oldimageurl'][] = $value[0];
						if(!isset($existentimg[$hash])) {
							$existentimg[$hash] = $imageurl;
							if(!($aid = forum_downremote($imageurl))){
								continue;
							}
							$_GET['attachnew'][$aid]['description'] = '';
							$attachaids[$hash] = $imagereplace['newimageurl'][] = '[attachimg]'.$aid.'[/attachimg]';
						} else {
							$imagereplace['newimageurl'][] = $attachaids[$hash];
						}
					}
				}
				$_GET['message'] = str_replace($imagereplace['oldimageurl'], $imagereplace['newimageurl'], $_GET['message']);
			}
		}
	}
}

class plugin_h5upload_group extends plugin_h5upload {

	function forumdisplay_fastpost_upload_extend_output() {
		global $_G, $swfconfig;
		$setconfig = $this->setconfig;
		$swfconfig[imageexts][mime] = $this->_get_mimeTypes($swfconfig[imageexts][ext]);
		$swfconfig[imageexts][ext] = str_replace(array("*.", ";"), array("", ","), $swfconfig[imageexts][ext]);
		$swfconfig[attachexts][mime] = $this->_get_mimeTypes($swfconfig[attachexts][ext]);
		$swfconfig[attachexts][ext] = str_replace(array("*.", ";"), array("", ","), $swfconfig[attachexts][ext]);
		$swfconfig["max"] = $swfconfig["max"]*1024;
		include template(self::$identifier.':fastpost_upload');
		if(!empty($_G['setting']['pluginhooks']['forumdisplay_fastpost_upload_extend'])){
			$_G['setting']['pluginhooks']['forumdisplay_fastpost_upload_extend'] = $return;
		}else{
			return $return;
		}
	}


	function viewthread_fastpost_upload_extend_output() {
		global $_G, $swfconfig;
		$setconfig = $this->setconfig;
		$swfconfig[imageexts][mime] = $this->_get_mimeTypes($swfconfig[imageexts][ext]);
		$swfconfig[imageexts][ext] = str_replace(array("*.", ";"), array("", ","), $swfconfig[imageexts][ext]);
		$swfconfig[attachexts][mime] = $this->_get_mimeTypes($swfconfig[attachexts][ext]);
		$swfconfig[attachexts][ext] = str_replace(array("*.", ";"), array("", ","), $swfconfig[attachexts][ext]);
		$swfconfig["max"] = $swfconfig["max"]*1024;
		include template(self::$identifier.':fastpost_upload');
		if(!empty($_G['setting']['pluginhooks']['viewthread_fastpost_upload_extend'])){
			$_G['setting']['pluginhooks']['viewthread_fastpost_upload_extend'] = $return;
		}else{
			return $return;
		}
	}

	function post_upload_extend_output() {
		global $_G, $swfconfig, $editorid, $allowpostimg, $special;
		if($special == 1){
			$_G['uploadjs'] = 1;
			if($_GET['action'] == 'newthread'){
				$_G['setting']['rewritestatus'] = '1';
				$_G['setting']['output']['str']['search']['addpolloption'] = 'addpolloption();';
				$_G['setting']['output']['str']['replace']['addpolloption'] = '';
			}
		}
		$setconfig = $this->setconfig;
		$swfconfig[imageexts][mime] = $this->_get_mimeTypes($swfconfig[imageexts][ext]);
		$swfconfig[imageexts][ext] = str_replace(array("*.", ";"), array("", ","), $swfconfig[imageexts][ext]);
		$swfconfig[attachexts][mime] = $this->_get_mimeTypes($swfconfig[attachexts][ext]);
		$swfconfig[attachexts][ext] = str_replace(array("*.", ";"), array("", ","), $swfconfig[attachexts][ext]);
		$swfconfig["max"] = $swfconfig["max"]*1024;
		include template(self::$identifier.':post_upload');
		if(!empty($_G['setting']['pluginhooks']['post_upload_extend'])){
			$_G['setting']['pluginhooks']['post_upload_extend'] = $return;
		}else{
			return $return;
		}
	}

	function post_poll_upload_extend_output() {
		global $_G, $swfconfig;
		$setconfig = $this->setconfig;
		$swfconfig["max"] = $swfconfig["max"]*1024;
		include template(self::$identifier.':post_poll_upload');
		if(!empty($_G['setting']['pluginhooks']['post_poll_upload_extend'])){
			$_G['setting']['pluginhooks']['post_poll_upload_extend'] = $return;
		}else{
			return $return;
		}
	}

	function post_sync_method() {
		global $_G;
		$setconfig = $this->setconfig;
		if((($_GET['action'] == 'newthread' && submitcheck('topicsubmit')) || ($_GET['action'] == 'reply' && submitcheck('replysubmit')) || ($_GET['action'] == 'edit' && submitcheck('editsubmit'))) && $setconfig['down_remote'] == 3 && $_G['group']['allowdownremoteimg']){
			//if($setconfig['ban_muma']){
				require DISCUZ_ROOT . './source/plugin/h5upload/lib/discuz_upload.php';
			//}
			require_once libfile('function/common', 'plugin/h5upload');
			preg_match_all("/\[img\]\s*([^\[\<\r\n]+?)\s*\[\/img\]|\[img=\d{1,4}[x|\,]\d{1,4}\]\s*([^\[\<\r\n]+?)\s*\[\/img\]/is", $_GET['message'], $image1, PREG_SET_ORDER);
			preg_match_all("/\<img.+src=('|\"|)?(.*)(\\1)([\s].*)?\>/ismUe", $_GET['message'], $image2, PREG_SET_ORDER);
			$temp = $aids = $existentimg = array();
			if(is_array($image1) && !empty($image1)) {
				foreach($image1 as $value) {
					$temp[] = array(
						'0' => $value[0],
						'1' => trim(!empty($value[1]) ? $value[1] : $value[2])
					);
				}
			}
			if(is_array($image2) && !empty($image2)) {
				foreach($image2 as $value) {
					$temp[] = array(
						'0' => $value[0],
						'1' => trim($value[2])
					);
				}
			}
			if(is_array($temp) && !empty($temp)) {
				$attachaids = array();
				foreach($temp as $value) {
					$imageurl = $value[1];
					$hash = md5($imageurl);
					if(strlen($imageurl)) {
						$imagereplace['oldimageurl'][] = $value[0];
						if(!isset($existentimg[$hash])) {
							$existentimg[$hash] = $imageurl;
							if(!($aid = forum_downremote($imageurl))){
								continue;
							}
							$_GET['attachnew'][$aid]['description'] = '';
							$attachaids[$hash] = $imagereplace['newimageurl'][] = '[attachimg]'.$aid.'[/attachimg]';
						} else {
							$imagereplace['newimageurl'][] = $attachaids[$hash];
						}
					}
				}
				$_GET['message'] = str_replace($imagereplace['oldimageurl'], $imagereplace['newimageurl'], $_GET['message']);
			}
		}
	}

}

class plugin_h5upload_portal extends plugin_h5upload {

	function portalcp_top_upload_extend_output() {
		global $_G, $swfconfig,$aid,$catid;
		$setconfig = $this->setconfig;
		$swfconfig[imageexts][mime] = $this->_get_mimeTypes($swfconfig[imageexts][ext]);
		$swfconfig[imageexts][ext] = str_replace(array("*.", ";"), array("", ","), $swfconfig[imageexts][ext]);
		$swfconfig[attachexts][mime] = $this->_get_mimeTypes($swfconfig[attachexts][ext]);
		$swfconfig[attachexts][ext] = str_replace(array("*.", ";"), array("", ","), $swfconfig[attachexts][ext]);
		$swfconfig["max"] = $swfconfig["max"]*1024;
		include template(self::$identifier.':portal_upload');
		if(!empty($_G['setting']['pluginhooks']['portalcp_top_upload_extend'])){
			$_G['setting']['pluginhooks']['portalcp_top_upload_extend'] = $return;
		}else{
			return $return;
		}
	}

	function attachment() {
		global $_G;
		$setconfig = $this->setconfig;
		if($_GET['op'] == 'getattach' && $_GET['type'] == 'attach') {
			$id = empty($_GET['id']) ? 0 : intval($_GET['id']);
			if($id && $attach = C::t('portal_attachment')->fetch($id)) {
				require_once libfile('function/attachment');
				if($attach['isimage']) {
					require_once libfile('function/home');
					$smallimg = pic_get($attach['attachment'], 'portal', $attach['thumb'], $attach['remote']);
					$bigimg = pic_get($attach['attachment'], 'portal', 0, $attach['remote']);
					$coverstr = addslashes(serialize(array('pic'=>'portal/'.$attach['attachment'], 'thumb'=>$attach['thumb'], 'remote'=>$attach['remote'])));
				}
				$attach['ext'] = $attach['filetype'];
				$attach['filetype'] = attachtype($attach['filetype']."\t".$attach['filetype']);
				$attach['filesize'] = sizecount($attach['filesize']);
				$attachmcode = $this->_parseattachmedia($attach);
				include template(self::$identifier.':portal_attachment');
				exit;
			}
		}
	}

	function _parseattachmedia($attach) {
		global $_G;
		$attachurl = ($attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl']).'portal/'.$attach['attachment'];
		$attachurl = (preg_match('/^https?:\/\//is', $attachurl) ? '' : $_G['siteurl']) . $attachurl;
		switch(strtolower($attach['ext'])) {
			case 'mp3':
			case 'wma':
			case 'ra':
			case 'ram':
			case 'wav':
			case 'mid':
				return '[flash=mp3]'.$attachurl.'[/flash]';
			case 'wmv':
			case 'rm':
			case 'rmvb':
			case 'avi':
			case 'asf':
			case 'mpg':
			case 'mpeg':
			case 'mov':
			case 'flv':
			case 'swf':
			case 'mp4':
			case 'ogg':
			case 'webm':
			case 'aac':
			case 'flac':
				return '[flash=media]'.$attachurl.'[/flash]';
			default:
				return;
		}
	}

	function portalcp_extend() {
		global $_G;
		$setconfig = $this->setconfig;
		if($_GET['ac'] == 'article' && submitcheck('articlesubmit') && $setconfig['down_remote'] == 3 && $_G['group']['allowdownremoteimg']){
			//if($setconfig['ban_muma']){
				require DISCUZ_ROOT . './source/plugin/h5upload/lib/discuz_upload.php';
			//}
			require_once libfile('function/common', 'plugin/h5upload');
			$arrayimageurl = $temp = $imagereplace = array();
			preg_match_all("/\<img.+src=('|\"|)?(.*)(\\1)([\s].*)?\>/ismUe", $_POST['content'], $temp, PREG_SET_ORDER);
			if(is_array($temp) && !empty($temp)) {
				foreach($temp as $tempvalue) {
					$tempvalue[2] = str_replace('\"', '', $tempvalue[2]);
					if(strlen($tempvalue[2])){
						$arrayimageurl[] = $tempvalue[2];
					}
				}
				$arrayimageurl = array_unique($arrayimageurl);
				if($arrayimageurl) {
					foreach($arrayimageurl as $tempvalue) {
						$imageurl = $tempvalue;
						if(!($attach = portal_downremote($imageurl))){
							continue;
						}
						$imagereplace['oldimageurl'][] = $imageurl;
						$_POST['attach_ids'] .= ','.$attach['attachid'];
						$newimageurl = $attach['url'].$attach['attachment'];
						$imagereplace['newimageurl'][] = $newimageurl;
					}
				}
			}
			if($imagereplace) {
				$_POST['content'] = preg_replace(array("/\<(script|style|iframe)[^\>]*?\>.*?\<\/(\\1)\>/si", "/\<!*(--|doctype|html|head|meta|link|body)[^\>]*?\>/si"), '', $_POST['content']);
				$_POST['content'] = str_replace($imagereplace['oldimageurl'], $imagereplace['newimageurl'], $_POST['content']);
			}
		}
	}
}

class plugin_h5upload_home extends plugin_h5upload {

	function spacecp_blog_upload_extend_output() {
		global $_G, $swfconfig;
		$setconfig = $this->setconfig;
		$swfconfig[imageexts][mime] = $this->_get_mimeTypes($swfconfig[imageexts][ext]);
		$swfconfig[imageexts][ext] = str_replace(array("*.", ";"), array("", ","), $swfconfig[imageexts][ext]);
		$swfconfig[attachexts][mime] = $this->_get_mimeTypes($swfconfig[attachexts][ext]);
		$swfconfig[attachexts][ext] = str_replace(array("*.", ";"), array("", ","), $swfconfig[attachexts][ext]);
		$swfconfig["max"] = $swfconfig["max"]*1024;
		include template(self::$identifier.':portal_upload');
		if(!empty($_G['setting']['pluginhooks']['spacecp_blog_upload_extend'])){
			$_G['setting']['pluginhooks']['spacecp_blog_upload_extend'] = $return;
		}else{
			return $return;
		}
	}

	function spacecp_upload_extend_output() {
		global $_G, $swfconfig;
		$setconfig = $this->setconfig;
		$swfconfig[imageexts][mime] = $this->_get_mimeTypes($swfconfig[imageexts][ext]);
		$swfconfig[imageexts][ext] = str_replace(array("*.", ";"), array("", ","), $swfconfig[imageexts][ext]);
		$swfconfig[attachexts][mime] = $this->_get_mimeTypes($swfconfig[attachexts][ext]);
		$swfconfig[attachexts][ext] = str_replace(array("*.", ";"), array("", ","), $swfconfig[attachexts][ext]);
		$swfconfig["max"] = $swfconfig["max"]*1024;
		include template(self::$identifier.':spacecp_upload');
		if(!empty($_G['setting']['pluginhooks']['spacecp_upload_extend'])){
			$_G['setting']['pluginhooks']['spacecp_upload_extend'] = $return;
		}else{
			return $return;
		}
	}

	function follow_upload_extend_output() {
		global $_G, $swfconfig, $defaultforum;
		$setconfig = $this->setconfig;
		$swfconfig[imageexts][mime] = $this->_get_mimeTypes($swfconfig[imageexts][ext]);
		$swfconfig[imageexts][ext] = str_replace(array("*.", ";"), array("", ","), $swfconfig[imageexts][ext]);
		$swfconfig[attachexts][mime] = $this->_get_mimeTypes($swfconfig[attachexts][ext]);
		$swfconfig[attachexts][ext] = str_replace(array("*.", ";"), array("", ","), $swfconfig[attachexts][ext]);
		$swfconfig["max"] = $swfconfig["max"]*1024;
		$dmfid = $_G['setting']['followforumid'] && !empty($defaultforum) ? $_G['setting']['followforumid'] : 0;;
		include template(self::$identifier.':follow_upload');
		if(!empty($_G['setting']['pluginhooks']['follow_upload_extend'])){
			$_G['setting']['pluginhooks']['follow_upload_extend'] = $return;
		}else{
			return $return;
		}
	}

}

class plugin_h5upload_misc extends plugin_h5upload {

	function swfupload() {
		global $_G;
		$setconfig = $this->setconfig;
		//if($setconfig['ban_muma']){
			require DISCUZ_ROOT . './source/plugin/h5upload/lib/discuz_upload.php';
		//}
	}

}

?>
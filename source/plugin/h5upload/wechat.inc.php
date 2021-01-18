<?php

/**
 *      $author: ³ËÁ¹ $
 */

if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$setconfig = $_G['cache']['plugin'][CURMODULE];

$_G['uid'] = intval($_POST['uid']);

if(empty($_G['uid']) || $_POST['hash'] != md5(substr(md5($_G['config']['security']['authkey']), 8).$_G['uid'])) {
	exit();
}

$mid = $_GET['mid'];
if(empty($mid)) {
	exit();
}

require_once DISCUZ_ROOT . './source/plugin/h5upload/lib/wechat.class.php';
$wechat_client = new h5upload_wechat($setconfig['wechat_appid'], $setconfig['wechat_appsecret']);
if(!$setconfig['access_token']){
	$wechat_client->setNoCache("AccessToken");
}
$url = $wechat_client->download($mid);

if($_GET['operation'] == 'upload') {
	if(!$url){
		echo 'DISCUZUPLOAD|1|10|0|1|||';
		exit();
	}
	$thumb = $remote = $width = 0;
	$error_sizelimit = "";
	$upload = new discuz_upload();
	$attach = array();
	$attach['name'] = trim($_GET['name']);
	$attach['name'] = $attach['name'] ? $attach['name'] : 'wximage.jpg';
	$attach['extension'] = 'jpg';
	$attach['attachdir'] = $upload->get_target_dir('forum');
	$attach['attachment'] = $attach['attachdir'].$upload->get_target_filename('forum').'.'.$attach['extension'];
	$attach['target'] = getglobal('setting/attachdir').'./forum/'.$attach['attachment'];

    if($setconfig['upload_storage'] == 'qiniuyun' && file_exists(DISCUZ_ROOT.'./source/plugin/cl_qiniuyun/wechat.php')){
        @include_once DISCUZ_ROOT.'./source/plugin/cl_qiniuyun/wechat.php';
		if(!$attach['size']){
			echo 'DISCUZUPLOAD|1|2|'.$aid.'|1|'.$attach['attachment'].'|'.$attach['name'].'|'.$error_sizelimit;
			exit();
		}
    }else{
		file_put_contents($attach['target'], $url);
		$attach['size'] = filesize($attach['target']);
		if(!$attach['size']){
			echo 'DISCUZUPLOAD|1|2|'.$aid.'|1|'.$attach['attachment'].'|'.$attach['name'].'|'.$error_sizelimit;
			exit();
		}
		if($_G['setting']['showexif']) {
			require_once libfile('function/attachment');
			$exif = getattachexif(0, $attach['target']);
		}
		if($_G['setting']['thumbsource'] || $_G['setting']['thumbstatus']) {
			require_once libfile('class/image');
			$image = new image;
		}
		if($_G['setting']['thumbsource'] && $_G['setting']['sourcewidth'] && $_G['setting']['sourceheight']) {
			$thumb = $image->Thumb($attach['target'], '', $_G['setting']['sourcewidth'], $_G['setting']['sourceheight'], 1, 1) ? 1 : 0;
			$width = $image->imginfo['width'];
			$attach['size'] = $image->imginfo['size'];
		}
		if($_G['setting']['thumbstatus']) {
			$thumb = $image->Thumb($attach['target'], '', $_G['setting']['thumbwidth'], $_G['setting']['thumbheight'], $_G['setting']['thumbstatus'], 0) ? 1 : 0;
			$width = $image->imginfo['width'];
		}
		if($_G['setting']['thumbsource'] || !$_G['setting']['thumbstatus']) {
			list($width) = @getimagesize($attach['target']);
		}
    }
	updatemembercount($_G['uid'], array('todayattachs' => 1, 'todayattachsize' => $attach['size']));

	$aid = getattachnewaid($_G['uid']);
	$insert = array(
		'aid' => $aid,
		'dateline' => $_G['timestamp'],
		'filename' => dhtmlspecialchars(censor($attach['name'])),
		'filesize' => $attach['size'],
		'attachment' => $attach['attachment'],
		'isimage' => 1,
		'uid' => $_G['uid'],
		'thumb' => $thumb,
		'remote' => $remote,
		'width' => $width,
	);
	C::t('forum_attachment_unused')->insert($insert);
	if($_G['setting']['showexif']) {
		C::t('forum_attachment_exif')->insert($aid, $exif);
	}
	echo 'DISCUZUPLOAD|1|0|'.$aid.'|1|'.$attach['attachment'].'|'.$attach['name'].'|'.$error_sizelimit;
	exit();

} elseif($_GET['operation'] == 'portal') {

	$aid = intval($_POST['aid']);
	$catid = intval($_POST['catid']);
	$msg = '';
	$errorcode = 0;
	if(!$url){
		$errorcode = 4;
		echo "{\"aid\":0, \"errorcode\":$errorcode}";
		exit();
	}

	$error_sizelimit = "";
	$upload = new discuz_upload();
	$attach = array();
	$attach['name'] = trim($_GET['name']);
	$attach['name'] = $attach['name'] ? $attach['name'] : 'wximage.jpg';
	$attach['extension'] = 'jpg';
	$attach['attachdir'] = $upload->get_target_dir('portal');
	$attach['attachment'] = $attach['attachdir'].$upload->get_target_filename('portal').'.'.$attach['extension'];
	$attach['target'] = getglobal('setting/attachdir').'./portal/'.$attach['attachment'];
	$attach['isimage'] = 1;
	$attach['thumb'] = $attach['remote'] = 0;

    if($setconfig['upload_storage'] == 'qiniuyun' && file_exists(DISCUZ_ROOT.'./source/plugin/cl_qiniuyun/wechat.php')){
        @include_once DISCUZ_ROOT.'./source/plugin/cl_qiniuyun/wechat.php';
		if(!$attach['size']){
			$errorcode = 4;
			echo "{\"aid\":0, \"errorcode\":$errorcode}";
			exit();
		}
    }else{
		file_put_contents($attach['target'], $url);
		$attach['size'] = filesize($attach['target']);

		if($attach['isimage'] && empty($_G['setting']['portalarticleimgthumbclosed'])) {
			require_once libfile('class/image');
			$image = new image();
			$thumbimgwidth = $_G['setting']['portalarticleimgthumbwidth'] ? $_G['setting']['portalarticleimgthumbwidth'] : 300;
			$thumbimgheight = $_G['setting']['portalarticleimgthumbheight'] ? $_G['setting']['portalarticleimgthumbheight'] : 300;
			$attach['thumb'] = $image->Thumb($attach['target'], '', $thumbimgwidth, $thumbimgheight, 2);
			$image->Watermark($attach['target'], '', 'portal');
		}

		if(getglobal('setting/ftp/on') && ((!$_G['setting']['ftp']['allowedexts'] && !$_G['setting']['ftp']['disallowedexts']) || ($_G['setting']['ftp']['allowedexts'] && in_array($attach['ext'], $_G['setting']['ftp']['allowedexts'])) || ($_G['setting']['ftp']['disallowedexts'] && !in_array($attach['ext'], $_G['setting']['ftp']['disallowedexts']))) && (!$_G['setting']['ftp']['minsize'] || $attach['size'] >= $_G['setting']['ftp']['minsize'] * 1024)) {
			if(ftpcmd('upload', 'portal/'.$attach['attachment']) && (!$attach['thumb'] || ftpcmd('upload', 'portal/'.getimgthumbname($attach['attachment'])))) {
				@unlink($_G['setting']['attachdir'].'/portal/'.$attach['attachment']);
				@unlink($_G['setting']['attachdir'].'/portal/'.getimgthumbname($attach['attachment']));
				$attach['remote'] = 1;
			} else {
				if(getglobal('setting/ftp/mirror')) {
					@unlink($attach['target']);
					@unlink(getimgthumbname($attach['target']));
					$errorcode = 5;
				}
			}
		}
		$setarr = array(
			'uid' => $_G['uid'],
			'filename' => $attach['name'],
			'attachment' => $attach['attachment'],
			'filesize' => $attach['size'],
			'isimage' => $attach['isimage'],
			'thumb' => $attach['thumb'],
			'remote' => $attach['remote'],
			'filetype' => $attach['extension'],
			'dateline' => $_G['timestamp'],
			'aid' => $aid
		);
		$setarr['attachid'] = C::t('portal_attachment')->insert($setarr, true);
		if($attach['isimage']) {
			require_once libfile('function/home');
			$smallimg = pic_get($attach['attachment'], 'portal', $attach['thumb'], $attach['remote']);
			$bigimg = pic_get($attach['attachment'], 'portal', 0, $attach['remote']);
			$coverstr = addslashes(serialize(array('pic'=>'portal/'.$attach['attachment'], 'thumb'=>$attach['thumb'], 'remote'=>$attach['remote'])));
			echo "{\"aid\":$setarr[attachid], \"isimage\":$attach[isimage], \"smallimg\":\"$smallimg\", \"bigimg\":\"$bigimg\", \"errorcode\":$errorcode, \"cover\":\"$coverstr\"}";
			exit();
		} else {
			$fileurl = 'portal.php?mod=attachment&id='.$attach['attachid'];
			echo "{\"aid\":$setarr[attachid], \"isimage\":$attach[isimage], \"file\":\"$fileurl\", \"errorcode\":$errorcode}";
			exit();
		}
	}
}

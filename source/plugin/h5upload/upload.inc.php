<?php

/**
 *      $author: 乘凉 $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$setconfig = $_G['cache']['plugin'][CURMODULE];
//if($setconfig['ban_muma']){
	require DISCUZ_ROOT . './source/plugin/h5upload/lib/discuz_upload.php';
//}

$_G['uid'] = intval($_POST['uid']);

if((empty($_G['uid']) && $_GET['operation'] != 'upload') || $_POST['hash'] != md5(substr(md5($_G['config']['security']['authkey']), 8).$_G['uid'])) {
	exit();
} else {
	if($_G['uid']) {
		$_G['member'] = getuserbyuid($_G['uid']);
	}
	$_G['groupid'] = $_G['member']['groupid'];
	loadcache('usergroup_'.$_G['member']['groupid']);
	$_G['group'] = $_G['cache']['usergroup_'.$_G['member']['groupid']];
}

if($_GET['operation'] == 'upload') {

	$forumattachextensions = '';
	$fid = intval($_GET['fid']);
	if($fid) {
		$forum = $fid != $_G['fid'] ? C::t('forum_forum')->fetch_info_by_fid($fid) : $_G['forum'];
		if($forum['status'] == 3 && $forum['level']) {
			$levelinfo = C::t('forum_grouplevel')->fetch($forum['level']);
			if($postpolicy = $levelinfo['postpolicy']) {
				$postpolicy = dunserialize($postpolicy);
				$forumattachextensions = $postpolicy['attachextensions'];
			}
		} else {
			$forumattachextensions = $forum['attachextensions'];
		}
		if($forumattachextensions) {
			$_G['group']['attachextensions'] = $forumattachextensions;
		}
	}

	$md5value = $_GET['md5value'];
	$chunk = intval($_GET['chunk']);
	$chunks = intval($_GET['chunks']);
	$chunksize = intval($_GET['chunksize']);

	if($_GET['chunkop'] == 'checkchunk') {
		$result = 0;
		if($md5value) {
			$filepart = getglobal('setting/attachdir').'./forum/temp/'.$md5value.'/'.$chunk;
			if(file_exists($filepart) && filesize($filepart) == $chunksize){
				$result = 1;
			}
		}
		//include template(CURMODULE.':result');
		echo $result;
		exit;
	}

	if($_GET['chunkop'] == 'checkchunks') {
		if($md5value) {
			$folderpath = getglobal('setting/attachdir').'./forum/temp/'.$md5value;
			$block_info = scandir($folderpath);
			if($block_info) {
				// 除去无用文件
				foreach ($block_info as $key => $block) {
					if ($block == '.' || $block == '..' || $block == 'index.html' || filesize($folderpath.'/'.$block) != $chunksize) unset($block_info[$key]);
				}
				helper_output::json(array('data' => array_values($block_info)));
				// echo json_encode($block_info);
			}
		}
		exit;
	}

	if($chunks > 1 && $md5value){
		$_FILES['Filedata']['name'] = diconv(urldecode($_FILES['Filedata']['name']), 'UTF-8');
		$_FILES['Filedata']['type'] = $_GET['filetype'];
		$upload = new discuz_upload();
		$upload->init($_FILES['Filedata'], 'forum');
		$attachment = $upload->attach['attachment'];

		$upload->attach['attachdir'] = 'temp/'.$md5value.'/';
		$upload->check_dir_exists('forum', 'temp', $md5value);
		$upload->attach['attachment'] = $upload->attach['attachdir'].$chunk;
		$upload->attach['target'] = getglobal('setting/attachdir').'./'.$upload->type.'/'.$upload->attach['attachment'];
		$upload->save();

		$block_info = scandir(getglobal('setting/attachdir').'./forum/temp/'.$md5value);
		// 除去无用文件
		foreach ($block_info as $key => $block) {
			if ($block == '.' || $block == '..' || $block == 'index.html') unset($block_info[$key]);
		}
		if(count($block_info) >= $chunks){
			$upload->attach['attachment'] = $attachment;
			$upload->attach['target'] = getglobal('setting/attachdir').'./'.$upload->type.'/'.$upload->attach['attachment'];
			$fp = fopen($upload->attach['target'], "wb+");
			for($i = 0; $i < $chunks; $i++){
				$filepart = getglobal('setting/attachdir').'./'.$upload->type.'/temp/'.$md5value.'/'.$i;
				$chunkFile = fopen($filepart, 'rb');  
				$content = fread($chunkFile, filesize($filepart));  
				fclose($chunkFile);  
				fwrite($fp, $content); 
				unset($chunkFile); 
			}
			@fclose($fp);  
			delDirAndFile(getglobal('setting/attachdir').'./'.$upload->type.'/temp/'.$md5value);

			$upload->attach['size'] = filesize($upload->attach['target']);

			$allowupload = !$_G['group']['maxattachnum'] || $_G['group']['maxattachnum'] && $_G['group']['maxattachnum'] > getuserprofile('todayattachs');;
			if(!$allowupload) {
					forum_uploadmsg(6);
			}

			if($_G['group']['attachextensions'] && (!preg_match("/(^|\s|,)".preg_quote($upload->attach['ext'], '/')."($|\s|,)/i", $_G['group']['attachextensions']) || !$upload->attach['ext'])) {
					forum_uploadmsg(1);
			}

			if(empty($upload->attach['size'])) {
					forum_uploadmsg(2);
			}

			if($_G['group']['maxattachsize'] && $upload->attach['size'] > $_G['group']['maxattachsize']) {
					forum_uploadmsg(3, $_G['group']['maxattachsize']);
			}

			loadcache('attachtype');
			if($_G['fid'] && isset($_G['cache']['attachtype'][$_G['fid']][$upload->attach['ext']])) {
				$maxsize = $_G['cache']['attachtype'][$_G['fid']][$upload->attach['ext']];
			} elseif(isset($_G['cache']['attachtype'][0][$upload->attach['ext']])) {
				$maxsize = $_G['cache']['attachtype'][0][$upload->attach['ext']];
			}
			if(isset($maxsize)) {
				if(!$maxsize) {
					forum_uploadmsg(4, 'ban');
				} elseif($upload->attach['size'] > $maxsize) {
					forum_uploadmsg(5, $maxsize);
				}
			}

			if($upload->attach['size'] && $_G['group']['maxsizeperday']) {
				$todaysize = getuserprofile('todayattachsize') + $upload->attach['size'];
				if($todaysize >= $_G['group']['maxsizeperday']) {
					forum_uploadmsg(11, 'perday|'.$_G['group']['maxsizeperday']);
				}
			}

			updatemembercount($_G['uid'], array('todayattachs' => 1, 'todayattachsize' => $upload->attach['size']));

			$thumb = $remote = $width = 0;
			if($_GET['type'] == 'image' && !$upload->attach['isimage']) {
				forum_uploadmsg(7);
			}
			if($upload->attach['isimage']) {
				if(!in_array($upload->attach['imageinfo']['2'], array(1,2,3,6))) {
					forum_uploadmsg(7);
				}
				if($_G['setting']['showexif']) {
					require_once libfile('function/attachment');
					$exif = getattachexif(0, $upload->attach['target']);
				}
				if($_G['setting']['thumbsource'] || $_G['setting']['thumbstatus']) {
					require_once libfile('class/image');
					$image = new image;
				}
				if($_G['setting']['thumbsource'] && $_G['setting']['sourcewidth'] && $_G['setting']['sourceheight']) {
					$thumb = $image->Thumb($upload->attach['target'], '', $_G['setting']['sourcewidth'], $_G['setting']['sourceheight'], 1, 1) ? 1 : 0;
					$width = $image->imginfo['width'];
					$upload->attach['size'] = $image->imginfo['size'];
				}
				if($_G['setting']['thumbstatus']) {
					$thumb = $image->Thumb($upload->attach['target'], '', $_G['setting']['thumbwidth'], $_G['setting']['thumbheight'], $_G['setting']['thumbstatus'], 0) ? 1 : 0;
					$width = $image->imginfo['width'];
				}
				if($_G['setting']['thumbsource'] || !$_G['setting']['thumbstatus']) {
					list($width) = @getimagesize($upload->attach['target']);
				}
				if($setconfig['ban_muma'] && $upload->checkHex($upload->attach['target'])){
					$upload->redrawImage($upload->attach['target']);
				}
			}
			if($_GET['type'] != 'image' && $upload->attach['isimage']) {
				$upload->attach['isimage'] = -1;
			}
			$aid = getattachnewaid($_G['uid']);
			$insert = array(
				'aid' => $aid,
				'dateline' => $_G['timestamp'],
				'filename' => dhtmlspecialchars(censor($upload->attach['name'])),
				'filesize' => $upload->attach['size'],
				'attachment' => $upload->attach['attachment'],
				'isimage' => $upload->attach['isimage'],
				'uid' => $_G['uid'],
				'thumb' => $thumb,
				'remote' => $remote,
				'width' => $width,
			);
			C::t('forum_attachment_unused')->insert($insert);

			if($upload->attach['isimage'] && $_G['setting']['showexif']) {
				C::t('forum_attachment_exif')->insert($aid, $exif);
			}
			forum_uploadmsg(0, '', $aid, $upload->attach);
		}
	}

} elseif($_GET['operation'] == 'portal') {

	$aid = intval($_POST['aid']);
	$catid = intval($_POST['catid']);
	$msg = '';
	$errorcode = 0;
	require_once libfile('function/portalcp');
	if($aid) {
		$article = C::t('portal_article_title')->fetch($aid);
		if(!$article) {
			$errorcode = 1;
		}

		if(check_articleperm($catid, $aid, $article, false, true) !== true) {
			$errorcode = 2;
		}

	} else {
		if(check_articleperm($catid, $aid, null, false, true) !== true) {
			$errorcode = 3;
		}
	}

	$md5value = $_GET['md5value'];
	$chunk = intval($_GET['chunk']);
	$chunks = intval($_GET['chunks']);
	$chunksize = intval($_GET['chunksize']);

	if($_GET['chunkop'] == 'checkchunk') {
		$result = 0;
		if($md5value) {
			$filepart = getglobal('setting/attachdir').'./portal/temp/'.$md5value.'/'.$chunk;
			if(file_exists($filepart) && filesize($filepart) == $chunksize){
				$result = 1;
			}
		}
		//include template(CURMODULE.':result');
		echo $result;
		exit;
	}

	if($_GET['chunkop'] == 'checkchunks') {
		if($md5value) {
			$folderpath = getglobal('setting/attachdir').'./portal/temp/'.$md5value;
			$block_info = scandir($folderpath);
			if($block_info) {
				// 除去无用文件
				foreach ($block_info as $key => $block) {
					if ($block == '.' || $block == '..' || $block == 'index.html' || filesize($folderpath.'/'.$block) != $chunksize) unset($block_info[$key]);
				}
				helper_output::json(array('data' => array_values($block_info)));
				// echo json_encode($block_info);
			}
		}
		exit;
	}

	if($chunks > 1 && $md5value){
		$_FILES['Filedata']['name'] = diconv(urldecode($_FILES['Filedata']['name']), 'UTF-8');
		$_FILES['Filedata']['type'] = $_GET['filetype'];
		$upload = new discuz_upload();
		$upload->init($_FILES['Filedata'], 'portal');
		$attachment = $upload->attach['attachment'];

		$upload->attach['attachdir'] = 'temp/'.$md5value.'/';
		$upload->check_dir_exists('portal', 'temp', $md5value);
		$upload->attach['attachment'] = $upload->attach['attachdir'].$chunk;
		$upload->attach['target'] = getglobal('setting/attachdir').'./'.$upload->type.'/'.$upload->attach['attachment'];
		if($upload->error()) {
			$errorcode = 4;
		}else{
			$upload->save();
		}

		$block_info = scandir(getglobal('setting/attachdir').'./portal/temp/'.$md5value);
		// 除去无用文件
		foreach ($block_info as $key => $block) {
			if ($block == '.' || $block == '..' || $block == 'index.html') unset($block_info[$key]);
		}
		if(count($block_info) >= $chunks){
			$upload->attach['attachment'] = $attachment;
			$upload->attach['target'] = getglobal('setting/attachdir').'./'.$upload->type.'/'.$upload->attach['attachment'];
			$fp = fopen($upload->attach['target'], "wb+");
			for($i = 0; $i < $chunks; $i++){
				$filepart = getglobal('setting/attachdir').'./'.$upload->type.'/temp/'.$md5value.'/'.$i;
				$chunkFile = fopen($filepart, 'rb');  
				$content = fread($chunkFile, filesize($filepart));  
				fclose($chunkFile);  
				fwrite($fp, $content); 
				unset($chunkFile); 
			}
			@fclose($fp);  
			delDirAndFile(getglobal('setting/attachdir').'./'.$upload->type.'/temp/'.$md5value);

			$upload->attach['size'] = filesize($upload->attach['target']);
			$attach = $upload->attach;

			if(!$errorcode) {
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

				if($setconfig['ban_muma'] && $attach['isimage'] && $upload->checkHex($attach['target'])){
					$upload->redrawImage($attach['target']);
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
			} else {
				echo "{\"aid\":0, \"errorcode\":$errorcode}";
			}
		}
	}

}

function forum_uploadmsg($statusid, $error_sizelimit = '', $aid = 0, $attach = array()) {
	global $_G,$setconfig;
	$error_sizelimit = !empty($error_sizelimit) ? $error_sizelimit : 0;
	$simple = !empty($_GET['simple']) ? $_GET['simple'] : 0;
	if($simple == 1) {
		echo 'DISCUZUPLOAD|'.$statusid.'|'.$aid.'|'.$attach['isimage'].'|'.$error_sizelimit;
	} elseif($simple == 2) {
		echo 'DISCUZUPLOAD|'.($_GET['type'] == 'image' ? '1' : '0').'|'.$statusid.'|'.$aid.'|'.$attach['isimage'].'|'.($attach['isimage'] ? $setconfig['bindDomain'].'forum/'.$attach['attachment'] : '').'|'.$attach['name'].'|'.$error_sizelimit;
	} else {
		echo $statusid ? -$statusid : $aid;
	}
	exit;
}

function delDirAndFile($path, $delDir = true) {  
    if (is_array($path)) {  
        foreach ($path as $subPath)  
            delDirAndFile($subPath, $delDir);  
    }  
    if (is_dir($path)) {  
        $handle = opendir($path);  
        if ($handle) {  
            while (false !== ( $item = readdir($handle) )) {  
                if ($item != "." && $item != "..")  
                    is_dir("$path/$item") ? delDirAndFile("$path/$item", $delDir) : unlink("$path/$item");  
            }  
            closedir($handle);  
            if ($delDir)  
                return rmdir($path);  
        }  
    } else {  
        if (file_exists($path)) {  
            return unlink($path);  
        } else {  
            return FALSE;  
        }  
    }  
    clearstatcache();  
} 
?>
<?php

/**
 *      $author: ณหมน $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function forum_downremote($imageurl) {
    global $_G, $setconfig;
    $attach = array();
    $thumb = $remote = $width = 0;

    if(!preg_match('/^(http(s?):\/\/|\.)/i', $imageurl)) {
        return false;
    }
	$host = parse_url($imageurl, PHP_URL_HOST);
	if(!$host || in_array($host, $setconfig['white_domain'])) {
		return false;
	}
    $mimes=array(
        'image/bmp'=>'bmp',
        'image/gif'=>'gif',
        'image/jpeg'=>'jpg',
        'image/png'=>'png',
        'image/webp'=>'jpg'
    );
	//$headers = get_headers($imageurl, 1);
	$headers = dget_headers($imageurl);
    if($headers === false){
        return false;
    }
    $type = $headers['Content-Type'];
    $fileext = '';
    foreach($mimes as $key => $value){
        if(strpos($type, $key) !== false) {
            $fileext = $value;
			break;
        }
    }
    if(empty($fileext)){
        return false;
    }
    $attach['ext'] = $fileext;

    $upload = new discuz_upload();
    $attach['isimage'] = $upload -> is_image_ext($attach['ext']);
    $attach['extension'] = $upload -> get_target_extension($attach['ext']);
    $attach['attachdir'] = $upload -> get_target_dir('forum');
    $attach['attachment'] = $attach['attachdir'] . $upload->get_target_filename('forum').'.'.$attach['extension'];
    $attach['target'] = getglobal('setting/attachdir').'./forum/'.$attach['attachment'];
    $attach['name'] = basename($imageurl);
    $attach['thumb'] = '';

    if($setconfig['upload_storage'] == 'qiniuyun' && file_exists(DISCUZ_ROOT.'./source/plugin/cl_qiniuyun/downremoteforum.php')){
        @include_once DISCUZ_ROOT.'./source/plugin/cl_qiniuyun/downremoteforum.php';

    }else{
        $content = dfsockopen($imageurl);
        if(empty($content)) return false;
        if(!@$fp = fopen($attach['target'], 'wb')) {
            return false;
        } else {
            flock($fp, 2);
            fwrite($fp, $content);
            fclose($fp);
        }
        if($type == 'image/webp' && function_exists('imagecreatefromwebp')) {
            $im = imagecreatefromwebp($attach['target']);
            imagejpeg($im, $attach['target'], 100);
            imagedestroy($im);
        }
        if(!$upload->get_image_info($attach['target'])) {
            @unlink($attach['target']);
            return false;
        }
        $attach['size'] = filesize($attach['target']);
        if($attach['isimage']) {
            if($_G['setting']['thumbsource'] && $_G['setting']['sourcewidth'] && $_G['setting']['sourceheight']) {
                $image = new image();
                $thumb = $image->Thumb($attach['target'], '', $_G['setting']['sourcewidth'], $_G['setting']['sourceheight'], 1, 1) ? 1 : 0;
                $width = $image->imginfo['width'];
                $attach['size'] = $image->imginfo['size'];
            }
            if($_G['setting']['thumbstatus']) {
                $image = new image();
                $thumb = $image->Thumb($attach['target'], '', $_G['setting']['thumbwidth'], $_G['setting']['thumbheight'], $_G['setting']['thumbstatus'], 0) ? 1 : 0;
                $width = $image->imginfo['width'];
            }
            if($_G['setting']['thumbsource'] || !$_G['setting']['thumbstatus']) {
                list($width) = @getimagesize($attach['target']);
            }
            if($_G['setting']['watermarkstatus'] && empty($_G['forum']['disablewatermark'])) {
                //$image = new image();
                //$image->Watermark($attach['target'], '', 'forum');
                //$attach['size'] = $image->imginfo['size'];
            }
			if($setconfig['ban_muma'] && $upload->checkHex($upload->attach['target'])){
				$upload->redrawImage($upload->attach['target']);
			}
        }
    }
    if($attach['ext'] != $upload->fileext($imageurl)) {
        $attach['name'] .= '.' . $attach['ext'];
    }

    $aid = getattachnewaid();
    $setarr = array(
        'aid' => $aid,
        'dateline' => $_G['timestamp'],
        'filename' => $attach['name'],
        'filesize' => $attach['size'],
        'attachment' => $attach['attachment'],
        'isimage' => $attach['isimage'],
        'uid' => $_G['uid'],
        'thumb' => $thumb,
        'remote' => $remote,
        'width' => $width
    );
    C::t("forum_attachment_unused")->insert($setarr);
    return $aid;
}

function portal_downremote($imageurl) {
    global $_G, $aid, $setconfig;

    if(!preg_match('/^(http(s?):\/\/|\.)/i', $imageurl)) {
        return false;
    }
	$host = parse_url($imageurl, PHP_URL_HOST);
	if(!$host || in_array($host, $setconfig['white_domain'])) {
		return false;
	}
    $mimes = array(
        'image/bmp'=>'bmp',
        'image/gif'=>'gif',
        'image/jpeg'=>'jpg',
        'image/png'=>'png',
        'image/webp'=>'jpg'
    );
	//$headers = get_headers($imageurl, 1);
	$headers = dget_headers($imageurl);
    if($headers === false){
        return false;
    }
    $type = $headers['Content-Type'];
    $fileext = '';
    foreach($mimes as $key => $value){
        if(strpos($type, $key) !== false) {
            $fileext = $value;
			break;
        }
    }
    if(empty($fileext)){
        return false;
    }
    $attach['ext'] = $fileext;

    $upload = new discuz_upload();
    $attach['isimage'] = $upload -> is_image_ext($attach['ext']);
    $attach['extension'] = $upload -> get_target_extension($attach['ext']);
    $attach['attachdir'] = $upload -> get_target_dir('portal');
    $attach['attachment'] = $attach['attachdir'] . $upload->get_target_filename('portal').'.'.$attach['extension'];
    $attach['target'] = getglobal('setting/attachdir').'./portal/'.$attach['attachment'];
    $attach['name'] = basename($imageurl);
    $attach['thumb'] = '';

    if($setconfig['upload_storage'] == 'qiniuyun' && file_exists(DISCUZ_ROOT.'./source/plugin/cl_qiniuyun/downremoteportal.php')){
        @include_once DISCUZ_ROOT.'./source/plugin/cl_qiniuyun/downremoteportal.php';

    }else{
        $content = dfsockopen($imageurl);
        if(empty($content)) return false;
        if(!@$fp = fopen($attach['target'], 'wb')) {
            return false;
        } else {
            flock($fp, 2);
            fwrite($fp, $content);
            fclose($fp);
        }
        if($type == 'image/webp' && function_exists('imagecreatefromwebp')) {
            $im = imagecreatefromwebp($attach['target']);
            imagejpeg($im, $attach['target'], 100);
            imagedestroy($im);
        }
        if(!$upload->get_image_info($attach['target'])) {
            @unlink($attach['target']);
            return false;
        }
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
                    portal_upload_error(lang('portalcp', 'upload_remote_failed'));
                }
            }
        }
		if($attach['isimage'] && $setconfig['ban_muma'] && $upload->checkHex($attach['target'])){
			$upload->redrawImage($attach['target']);
		}
    }
    if($attach['ext'] != $upload->fileext($imageurl)) {
        $attach['name'] .= '.' . $attach['ext'];
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

    $attach = $setarr;
    $attach['url'] = ($attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl']).'portal/';

    return $attach;

}

function blog_downremote($imageurl) {
    global $_G, $setconfig;

    if(!preg_match('/^(http(s?):\/\/|\.)/i', $imageurl)) {
        return false;
    }
	$host = parse_url($imageurl, PHP_URL_HOST);
	if(!$host || in_array($host, $setconfig['white_domain'])) {
		return false;
	}
    $mimes = array(
        'image/bmp'=>'bmp',
        'image/gif'=>'gif',
        'image/jpeg'=>'jpg',
        'image/png'=>'png',
        'image/webp'=>'jpg'
    );
	//$headers = get_headers($imageurl, 1);
	$headers = dget_headers($imageurl);
    if($headers === false){
        return false;
    }
    $type = $headers['Content-Type'];
    $fileext = '';
    foreach($mimes as $key => $value){
        if(strpos($type, $key) !== false) {
            $fileext = $value;
			break;
        }
    }
    if(empty($fileext)){
        return false;
    }
    $attach['ext'] = $fileext;

}

function dget_headers($imageurl) {
	$timeout = 15;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_URL, $imageurl);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
	$data = curl_exec($ch);
	$status = curl_getinfo($ch);
	$errno = curl_errno($ch);
    curl_close($ch);
	if($errno || $status['http_code'] != 200) {
		return false;
	} else {
		$headers = substr($data, 0, $status['header_size']);
    	$head_data = preg_split('/\n/',$headers);
    	$head_data = array_filter($head_data);
    	$headers_arr = array();
    	foreach($head_data as $val){
        	list($k,$v) = explode(":",$val);
        	$headers_arr[$k] = $v;
    	}
		return $headers_arr ? $headers_arr : false;
    }
}

?>
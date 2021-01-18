<?php

/**
 *      $author: ³ËÁ¹ $
 */

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

if(empty($_G['uid'])) {
    exit();
}

$setconfig = $_G['cache']['plugin'][CURMODULE];
if($setconfig['ban_muma']){
	require DISCUZ_ROOT . './source/plugin/h5upload/lib/discuz_upload.php';
}
$websiteArr = array();
if($setconfig['white_domain']){
	foreach(explode("\n", $setconfig['white_domain']) as $key => $option) {
		$option = trim($option);
		if($option){
			$websiteArr[] = $option;
		}
	}
}
$setconfig['white_domain'] = $websiteArr;

if(!$setconfig['down_remote']) {
    dexit();
}

require_once libfile('class/image');

if($_GET['imgurl']) {

    $imageurl = $_GET['imgurl'];
	$returnval = down_remote_imgurl($imageurl);
    if(!$returnval){
        $returnval = 0;
    }
    include template(CURMODULE.':downremote');
    dexit();

}else{
    $_GET['message'] = str_replace(array("\r", "\n"), array($_GET['wysiwyg'] ? '<br />' : '', "\\n"), $_GET['message']);
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

                    if(!($aid = down_remote_imgurl($imageurl))){
                        continue;
                    }
                    $aids[] = $aid;
                    $attachaids[$hash] = $imagereplace['newimageurl'][] = '[attachimg]'.$aid.'[/attachimg]';

                } else {
                    $imagereplace['newimageurl'][] = $attachaids[$hash];
                }
            }
        }
        if(!empty($aids)) {
            require_once libfile('function/post');
        }
        $_GET['message'] = str_replace($imagereplace['oldimageurl'], $imagereplace['newimageurl'], $_GET['message']);
    }
    $_GET['message'] = addcslashes($_GET['message'], '/"\'');
    print <<<EOF
		<script type="text/javascript">
			parent.ATTACHORIMAGE = 1;
			parent.updateDownImageLists('$_GET[message]');
		</script>
EOF;
    dexit();
}

function down_remote_imgurl($imageurl) {
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
    $patharr = explode('/', $imageurl);
    $attach['name'] =  trim($patharr[count($patharr)-1]);
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
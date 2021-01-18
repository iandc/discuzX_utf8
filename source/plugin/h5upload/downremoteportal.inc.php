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

$aid = intval(getgpc('aid'));
$catid = intval(getgpc('catid'));
require_once libfile('function/home');
require_once libfile('function/portalcp');
if($aid) {
    $article = C::t('portal_article_title')->fetch($aid);
    if(!$article) {
        portal_upload_error(lang('portalcp', 'article_noexist'));
    }
    if(check_articleperm($catid, $aid, $article, false, true) !== true) {
        portal_upload_error(lang('portalcp', 'article_noallowed'));
    }
} else {
    if(($return = check_articleperm($catid, $aid, null, false, true)) !== true) {
        portal_upload_error(lang('portalcp', $return));
    }
}

require_once libfile('class/image');

if($_GET['imgurl']) {

    $imageurl = $_GET['imgurl'];
    $attach = down_remote_imgurl($imageurl);
    if($attach){
		if($attach['isimage']) {
			$smallimg = pic_get($attach['attachment'], 'portal', $attach['thumb'], $attach['remote']);
			$bigimg = pic_get($attach['attachment'], 'portal', 0, $attach['remote']);
			$coverstr = addslashes(serialize(array('pic'=>'portal/'.$attach['attachment'], 'thumb'=>$attach['thumb'], 'remote'=>$attach['remote'])));
			$returnval = "{\"aid\":$attach[attachid], \"isimage\":$attach[isimage], \"smallimg\":\"$smallimg\", \"bigimg\":\"$bigimg\", \"cover\":\"$coverstr\"}";
		} else {
			$fileurl = 'portal.php?mod=attachment&id='.$attach['attachid'];
			$returnval = "{\"aid\":$attach[attachid], \"isimage\":$attach[isimage], \"file\":\"$fileurl\"}";
		}
    } else {
		$returnval = "";
	}
    include template(CURMODULE.':downremote');
    dexit();

}else{

    $arrayimageurl = $temp = $imagereplace = array();
    $string = $_GET['content'];
    preg_match_all("/\<img.+src=('|\"|)?(.*)(\\1)([\s].*)?\>/ismUe", $string, $temp, PREG_SET_ORDER);
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
                $imagereplace['oldimageurl'][] = $imageurl;

                if(!($attach = down_remote_imgurl($imageurl))){
                    continue;
                }
                portal_upload_show($attach);
                $newimageurl = $attach['url'].$attach['attachment'];
                $imagereplace['newimageurl'][] = $newimageurl;
            }
        }
    }

    if($imagereplace) {
        $string = preg_replace(array("/\<(script|style|iframe)[^\>]*?\>.*?\<\/(\\1)\>/si", "/\<!*(--|doctype|html|head|meta|link|body)[^\>]*?\>/si"), '', $string);
        $string = str_replace($imagereplace['oldimageurl'], $imagereplace['newimageurl'], $string);
        $string = str_replace(array("\r", "\n", "\r\n"), '', addcslashes($string, '/"\\\''));
        print <<<EOF
		<script type="text/javascript">
			var f = parent.window.frames["uchome-ifrHtmlEditor"].window.frames["HtmlEditor"];
			f.document.body.innerHTML = '$string';
			parent.DOWNREMOTESTATUS = 0;
		</script>
EOF;
    }
    dexit();

}

function portal_upload_error($msg) {
    echo '<script>';
    echo 'if(parent.$(\'localfile_'.$_GET['attach_target_id'].'\') != null)parent.$(\'localfile_'.$_GET['attach_target_id'].'\').innerHTML = \''.lang('portalcp', 'upload_error').$msg.'\';else alert(\''.$msg.'\')';
    echo '</script>';
    exit();
}

function portal_upload_show($attach) {
    global $_G;

    $imagehtml = $filehtml = $coverstr ='';

    if($attach['isimage']) {
        $imagehtml = get_uploadcontent($attach, 'portal', 'upload');
        $coverstr = addslashes(serialize(array('pic'=>'portal/'.$attach['attachment'], 'thumb'=>$attach['thumb'], 'remote'=>$attach['remote'])));
    } else {
        $filehtml = get_uploadcontent($attach, 'portal', 'upload');
    }

    echo '<script type="text/javascript" src="'.$_G[setting][jspath].'handlers.js?'.$_G['style']['verhash'].'"></script>';
    echo '<script>';
    if($imagehtml) echo 'var tdObj = getInsertTdId(parent.$(\'imgattachlist\'), \'attach_list_'.$attach['attachid'].'\');tdObj.innerHTML = \''.addslashes($imagehtml).'\';';
    if($filehtml) echo 'parent.$(\'attach_file_body\').innerHTML = \''.addslashes($filehtml).'\'+parent.$(\'attach_file_body\').innerHTML;';
    echo 'if(parent.$(\'localfile_'.$_GET['attach_target_id'].'\') != null)parent.$(\'localfile_'.$_GET['attach_target_id'].'\').style.display = \'none\';';
    echo 'parent.$(\'attach_ids\').value += \','.$attach['attachid'].'\';';
    if($coverstr) echo 'if(parent.$(\'conver\').value == \'\')parent.$(\'conver\').value = \''.$coverstr.'\';';
    echo 'if(parent.$(\'imageuploaderdndtip\'))parent.$(\'imageuploaderdndtip\').style.display = \'none\';';
    echo '</script>';

}

function down_remote_imgurl($imageurl) {
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
    $patharr = explode('/', $imageurl);
    $attach['name'] =  trim($patharr[count($patharr)-1]);
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
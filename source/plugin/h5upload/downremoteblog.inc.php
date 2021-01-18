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
if(!$setconfig['down_remote']) {
    dexit();
}

require_once libfile('function/home');
require_once libfile('function/portalcp');

if(helper_access::check_module('album')) {


}

require_once libfile('class/image');

if($_GET['imgurl']) {

    $imageurl = $_GET['imgurl'];
    $attach = down_remote_imgurl($imageurl);
    if($attach){
		if($attach['isimage']) {
			$bigimg = pic_get($attach['attachment'], 'portal', 0, $attach['remote']);
			$returnval = "{\"aid\":$attach[attachid], \"url\":\"$url\", \"bigimg\":\"$bigimg\"}";
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
    global $_G, $aid;

    $mimes=array(
        'image/bmp'=>'bmp',
        'image/gif'=>'gif',
        'image/jpeg'=>'jpg',
        'image/png'=>'png',
        'image/webp'=>'jpg'
    );

    $upload = new discuz_upload();

    //$attach['ext'] = $upload->fileext($imageurl);
    //if(!$upload->is_image_ext($attach['ext'])) {
    //continue;
    //}
    $content = '';
    if(preg_match('/^(http:\/\/|\.)/i', $imageurl) || preg_match('/^(https:\/\/|\.)/i', $imageurl)) {
        if(($headers=get_headers($imageurl, 1))===false){
            return false;
        }
        $type=$headers['Content-Type'];
        if(!isset($mimes[$type])){
            return false;
        }

        $attach['ext'] = $mimes[$type];
        $content = dfsockopen($imageurl);
    } elseif(checkperm('allowdownlocalimg')) {
        if(preg_match('/^data\/(.*?)\.thumb\.jpg$/i', $imageurl)) {
            $attach['ext'] = $upload->fileext(substr($imageurl, 0, strrpos($imageurl, '.')-6));
            if(!$upload->is_image_ext($attach['ext'])) {
                continue;
            }
            $content = file_get_contents(substr($imageurl, 0, strrpos($imageurl, '.')-6));
        } elseif(preg_match('/^data\/(.*?)\.(jpg|jpeg|gif|png)$/i', $imageurl)) {
            $attach['ext'] = $upload->fileext($imageurl);
            if(!$upload->is_image_ext($attach['ext'])) {
                continue;
            }
            $content = file_get_contents($imageurl);
        }
    }
    if(empty($content)) return false;
    $temp = explode('/', $imageurl);

    $attach['name'] =  trim($temp[count($temp)-1]);
    $attach['thumb'] = '';
    $attach['isimage'] = $upload -> is_image_ext($attach['ext']);
    $attach['extension'] = $upload -> get_target_extension($attach['ext']);
    $attach['attachdir'] = $upload -> get_target_dir('portal');
    $attach['attachment'] = $attach['attachdir'] . $upload->get_target_filename('portal').'.'.$attach['extension'];
    $attach['target'] = getglobal('setting/attachdir').'./portal/'.$attach['attachment'];

    if(!@$fp = fopen($attach['target'], 'wb')) {
        return false;
    } else {
        flock($fp, 2);
        fwrite($fp, $content);
        fclose($fp);
    }
    if($type == 'image/webp') {
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

?>
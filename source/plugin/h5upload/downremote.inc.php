<?php

/**
 *      $author: ³ËÁ¹ $
 */

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

if(empty($_G['uid']) || !$_G['group']['allowdownremoteimg']) {
    exit();
}

require_once libfile('function/common', 'plugin/h5upload');
require_once libfile('class/image');

$setconfig = $_G['cache']['plugin'][CURMODULE];
if(!$setconfig['down_remote']) {
    dexit();
}
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

if($_GET['operation'] == 'forum') {
	if($_GET['imgurl']) {

    	$imageurl = $_GET['imgurl'];
		$returnval = forum_downremote($imageurl);
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

                    	if(!($aid = forum_downremote($imageurl))){
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

} elseif($_GET['operation'] == 'portal') {

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

	if($_GET['imgurl']) {

    	$imageurl = $_GET['imgurl'];
    	$attach = portal_downremote($imageurl);
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
                	if(!($attach = portal_downremote($imageurl))){
                    	continue;
                	}
                	$imagereplace['oldimageurl'][] = $imageurl;

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

} elseif($_GET['operation'] == 'album') {

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


?>
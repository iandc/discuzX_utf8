<?php

/**
 *      $author: ³ËÁ¹ $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$pluginurl = ADMINSCRIPT.'?action=plugins&identifier='.$plugin['identifier'].'&pmod=language';

$selectlang = array('script' => '&#31243;&#24207;&#33050;&#26412;', 'template' => '&#27169;&#26495;&#39029;&#38754;');
$type = in_array($_GET['type'], array_keys($selectlang)) ? $_GET['type'] : 'script';
loadcache('pluginlanguage_'.$type, 1);
if(empty($_G['cache']['pluginlanguage_'.$type])) {
	$_G['cache']['pluginlanguage_'.$type] = array();
}

if(!submitcheck('savesubmit')) {
	showformheader('plugins&identifier='.$plugin['identifier'].'&pmod=language&type='.$type);
	$headertab = '';
	foreach ($selectlang as $key => $value) {
		if($key == $type){
			$headertab .= '<div style="float:left;margin-right:10px;"><a href="'.$pluginurl.'&type='.$key.'" style="display:block;background:#555;color:#fff;padding:0 15px;line-height:25px;text-decoration:none">'.$value.'</a></div>';
		}else{
			$headertab .= '<div style="float:left;margin-right:10px;"><a href="'.$pluginurl.'&type='.$key.'" style="display:block;background:#ddd;padding:0 15px;line-height:25px;text-decoration:none">'.$value.'</a></div>';
		}
	}
	showtableheader($headertab);
	foreach ($_G['cache']['pluginlanguage_'.$type][$plugin['identifier']] as $key => $value) {
		if($type == 'template' || ($type == 'script' && strpos($key, $plugin['identifier']) === 0)){
			showsetting($key, "setting[$key]", $value, 'textarea');
		}
	}
	showsubmit('savesubmit', 'submit');
	showtablefooter();
	showformfooter();
} else {
	$_G['cache']['pluginlanguage_'.$type][$plugin['identifier']] = array_merge($_G['cache']['pluginlanguage_'.$type][$plugin['identifier']], $_GET['setting']);
	savecache('pluginlanguage_'.$type, $_G['cache']['pluginlanguage_'.$type]);
	if($type == 'template') {
		cleartemplatecache();
	}
	cpmsg('plugins_edit_succeed', 'action=plugins&identifier='.$plugin['identifier'].'&pmod=language&type='.$type, 'succeed');
}


?>
<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * From www.1314study.com
 * 应用售后问题：http://www.discuz.1314study.com/services.php?mod=ask&sid=1
 * 应用售前咨询：http://www.discuz.1314study.com/services.php?mod=ask&sid=2
 * 二次开发定制：http://www.discuz.1314study.com/services.php?mod=ask&sid=22
 */

if(!defined('IN_ADMINCP')) {
	exit('Access Denied');
}

if(empty($license)){
	if($operation == 'import'){
		$license = dfsockopen('http://addon.1314study.com/api/license.php?siteurl='.rawurlencode($_G['siteurl']).'&identifier='.$pluginarray['plugin']['identifier'], 0, '', '', false, '', 5);
		if(empty($_GET['license']) && $license) {
			$installtype = $_GET['installtype'];
			$dir = $_GET['dir'];
			require_once libfile('function/discuzcode');
			$pluginarray['license'] = discuzcode(strip_tags($pluginarray['license']), 1, 0);
			echo '<style type="text/css">.infobox button{color: #fff;background-color: #868789;border-color: #868789;display: inline-block;padding: 6px 12px;margin-bottom: 0;font-size: 14px;font-weight: 400;line-height: 1.42857143;text-align: center;white-space: nowrap;vertical-align: middle;-ms-touch-action: manipulation;touch-action: manipulation;cursor: pointer;-webkit-user-select: none;-moz-user-select: none;-ms-user-select: none;user-select: none;background-image: none;border: 1px solid transparent;border-radius: 4px;}.infobox .agree{background-color: #2a77c7;border-color: #2a77c7;}</style>'.
				'<div class="infobox"><h4 class="infotitle2">'.$pluginarray['plugin']['name'].' '.$pluginarray['plugin']['version'].' '.$lang['plugins_import_license'].'</h4><div style="text-align:left;line-height:25px;">'.$license.'</div><br /><br /><center>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.
				'<button onclick="location.href=\''.ADMINSCRIPT.'?action=plugins&operation=import&dir='.$dir.'&installtype='.$installtype.'&license=pas&formhash='.FORMHASH.'\'">'.$lang['plugins_import_pass'].'</button>&nbsp;&nbsp;&nbsp;&nbsp;'.
				'<button onclick="location.href=\''.ADMINSCRIPT.'?action=plugins&operation=import&dir='.$dir.'&installtype='.$installtype.'&license=yes&formhash='.FORMHASH.'\'" class="agree">'.$lang['plugins_import_agree'].'</button>&nbsp;&nbsp;&nbsp;&nbsp;<---&#x70B9;&#x51FB;&#x540C;&#x610F;&#x6388;&#x6743;&#x534F;&#x8BAE;&#xFF0C;&#x8FDB;&#x5165;&#x5E94;&#x7528;&#x5B89;&#x88C5;&#x6D41;&#x7A0B;</center></div>';
			exit;
		}
	}
}elseif($license == 'pas'){
	if(preg_match("/^[a-z]+[a-z0-9_]*$/i", $pluginarray['plugin']['identifier'])){
		cloudaddons_cleardir(DISCUZ_ROOT.'./source/plugin/'.$pluginarray['plugin']['identifier'].'/');
	}
	dheader('Location: '.ADMINSCRIPT.'?action=plugins');
	exit;
}

$addonid = $pluginarray['plugin']['identifier'].'.plugin';
$array = cloudaddons_getmd5($addonid);
if(cloudaddons_open('&mod=app&ac=validator&addonid='.$addonid.($array !== false ? '&rid='.$array['RevisionID'].'&sn='.$array['SN'].'&rd='.$array['RevisionDateline'] : '')) === '0') {
	if(preg_match("/^[a-z]+[a-z0-9_]*$/i", $pluginarray['plugin']['identifier'])){
		cloudaddons_cleardir(DISCUZ_ROOT.'./source/plugin/'.$pluginarray['plugin']['identifier'].'/');
	}
	cpmsg('clo'.'ud'.'addo'.'ns_genu'.'ine_mes'.'sage', '', 'error', array('addonid' => $addonid));
}
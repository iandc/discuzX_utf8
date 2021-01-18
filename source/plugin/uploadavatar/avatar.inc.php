<?php

/**
 *      $author: ³ËÁ¹ $
 */

if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(empty($_G['uid'])) {
	if($_SERVER['REQUEST_METHOD'] == 'GET') {
		dsetcookie('_refer', rawurlencode($_SERVER['REQUEST_URI']));
	} else {
		dsetcookie('_refer', rawurlencode('plugin.php?id='.CURMODULE.':avatar'));
	}
	showmessage('to_login', '', array(), array('showmsg' => true, 'login' => 1));
}

$setconfig = $_G['cache']['plugin'][CURMODULE];
$setconfig['allow_usergroups'] = (array)unserialize($setconfig['allow_usergroups']);
if(in_array('', $setconfig['allow_usergroups'])) {
	$setconfig['allow_usergroups'] = array();
}
if($setconfig['allow_usergroups'] && !in_array($_G['groupid'], $setconfig['allow_usergroups'])){
	showmessage(lang('plugin/'.CURMODULE, 'uploadavatar_usergroup_notallowed'));
}

$space = getuserbyuid($_G['uid']);
if(empty($space)) {
	showmessage('space_does_not_exist');
}
if(($space['status'] == -1 || in_array($space['groupid'], array(4, 5, 6)))) {
	showmessage('space_has_been_locked');
}

loaducenter();
$uc_avatarflash = uc_avatar($_G['uid'], 'virtual', 0);

if(empty($space['avatarstatus']) && uc_check_avatar($_G['uid'], 'middle')) {
	C::t('common_member')->update($_G['uid'], array('avatarstatus'=>'1'));

	updatecreditbyaction('setavatar');

	manyoulog('user', $_G['uid'], 'update');
}
if(defined('IN_MOBILE')) {
	$navtitle = lang('plugin/'.CURMODULE, 'uploadavatar_avatar');
	include template(CURMODULE.':avatar');
}else{
	dheader('location: home.php?mod=spacecp&ac=avatar');
}


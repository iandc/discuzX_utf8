<?php

/**
 *      $author: ณหมน $
 */

if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class mobileplugin_uploadavatar {

	public static $identifier = 'uploadavatar';

	function __construct() {
		global $_G;
		$setconfig = $_G['cache']['plugin'][self::$identifier];
		$setconfig['allow_usergroups'] = (array)unserialize($setconfig['allow_usergroups']);
		if(in_array('', $setconfig['allow_usergroups'])) {
			$setconfig['allow_usergroups'] = array();
		}
		$this->setconfig = $setconfig;
	}

	function global_footer_mobile() {
		global $_G, $_SERVER, $swfconfig;
		$setconfig = $this->setconfig;
		if(CURSCRIPT == 'home' && CURMODULE == 'spacecp' && $_GET['ac'] == 'avatar'){
			if(!$setconfig['allow_usergroups'] || in_array($_G['groupid'], $setconfig['allow_usergroups'])){
				dheader('location: plugin.php?id=uploadavatar:avatar');
				exit;
			}
		}
		if(CURSCRIPT == 'home' && CURMODULE == 'space' && $_GET['do'] == 'profile' && $_GET['mycenter']){
			if(!$setconfig['allow_usergroups'] || in_array($_G['groupid'], $setconfig['allow_usergroups'])){
				include template(self::$identifier.':mycenter');
			}
		}
		return $return;
	}
}

?>
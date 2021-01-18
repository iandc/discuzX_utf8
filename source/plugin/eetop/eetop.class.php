<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class plugin_doc_preview {

	var $value = [];

	function _construct() {
		global $_G;
		if(!$_G['uid']) {
			return 'guest';
		}
	}

}

class plugin_doc_preview_forum extends plugin_doc_preview {
    function viewthread_modaction_output() {
        global $_G;
        return 'a';
    }
}

?>
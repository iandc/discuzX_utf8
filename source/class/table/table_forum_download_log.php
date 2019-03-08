<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_forum_attachment.php 36278 2016-12-09 07:52:35Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_forum_download_log extends discuz_table
{
	private $_tableids = array();

	public function __construct() {

		$this->_table = 'forum_download_log';
		$this->_pk    = 'aid';
		$this->_pre_cache_key = 'forum_download_log_';
		$this->_cache_ttl = 0;

		parent::__construct();
	}

	public function count_by_aid($aid) {
		return $aid ? DB::result_first("SELECT COUNT(*) FROM %t WHERE aid=%d", array($this->_table, $aid)) : 0;
	}

    public function fetch_by_uid($uid) {
        $query = DB::query("SELECT * FROM %t WHERE uid=%d", array($this->_table, $uid));
        return DB::fetch($query);
    }

	public function fetch_by_aid_uid($aid, $uid) {
		$query = DB::query("SELECT * FROM %t WHERE aid=%d AND uid=%d", array($this->_table, $aid, $uid));
		return DB::fetch($query);
	}

}

?>
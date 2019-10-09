<?php
if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
header("Content-type: text/html; charset=utf-8");
require_once dirname(__FILE__)."/class/env.class.php";
$uid = isset($_GET['uid']) ? $_GET['uid'] : 1;
$profile = C::m('#xcblog#xcblog_profile')->getByUid($uid);
if (empty($profile)) {
    die("Sorry, this blog is closed.");
}
$profile['realname'] = $profile['username'];
$profile['realname'] = xcblog_utils::toutf8($profile['realname']);
$profile['avatar'] = avatar($uid,'middle',true);
$env = xcblog_env::getall();
$setting = C::m('#xcblog#xcblog_setting')->get();
$plugin_path = xcblog_env::get_plugin_path();
$filename = basename(__FILE__);
list($controller) = explode('.',$filename);
include template("xcblog:".strtolower($controller));
xcblog_env::getlog()->trace("pv[".$_G['username']."|uid:".$_G['uid']."]");
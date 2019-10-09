<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
require_once 'class/utils.class.php';
class plugin_xcblog
{
}
class plugin_xcblog_home extends plugin_xcblog
{
    function space_card_option_output()
    {
        global $_G;
        $uid = $GLOBALS["space"]["uid"];
        $title = lang('plugin/xcblog','myblog');
        return '<a href="plugin.php?id=xcblog&uid='.$uid.'" class="xi2" target="_blank">'.$title.'</a>';
    }
    function space_profile_baseinfo_middle_output()
    {
        global $_G;
        $uid = $GLOBALS["space"]["uid"];
        $title = lang('plugin/xcblog','myblog');
        return '<ul class="cl bbda pbm mbm">'.
            '<li><a href="plugin.php?id=xcblog&uid='.$uid.'" class="xi2">'.$title.'</a></li>'.
        '</ul>';
    }
}
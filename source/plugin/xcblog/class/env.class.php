<?php
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
require_once 'utils.class.php';
require_once 'log.class.php';
require_once 'validate.class.php';
class xcblog_env
{
    private static $_log_obj = null;
    public static function getall()
    {
        global $_G;
        $setting = $_G['setting'];
        $res = array (
            'discuz_version' => $setting['version'],
            'sitename' => xcblog_utils::toutf8($setting['sitename']),
            'bbname' => xcblog_utils::toutf8($setting['bbname']),
            'charset' => $_G['charset'],
            'ucenterurl' => $setting['ucenterurl'],
            'icp' => $setting['icp'],
            'mobile' => $setting['mobile'],
            'available_plugins' => $setting['plugins']['version'],
        );
        return $res;
    }
    public static function get_siteurl()
    {
        global $_G;
		$_G['siteurl'] = preg_replace("/source\/plugin\/xcblog/i","", $_G['siteurl']);
		return rtrim($_G['siteurl'], '/');
    }
    public static function get_sitename()
    {
        global $_G;
        $sitename = $_G["setting"]["sitename"];
        $charset = strtolower($_G['charset']);
        if ($charset=='gbk') {
            $sitename = xcblog_utils::toutf8($sitename);
        }
        return $sitename;
    }
    public static function get_admin_email()
    {
        global $_G;
        return $_G["setting"]["adminemail"];
    }
    public static function get_plugin_path()
    {
        return self::get_siteurl().'/source/plugin/xcblog';
    }
    public static function result(array $result,$json_header=true)
    {
        header("Content-type: application/json");
        if (!isset($result['retcode'])) {
            $result['retcode'] = 0;
        }
        if (!isset($result['retmsg'])) {
            $result['retmsg'] = 'succ';
        }
		if ($json_header) {
            header("Content-type: application/json");
		}
        if (CHARSET=='gbk') {
            self::encodeutf8($result);
        }
        echo json_encode($result);
        exit;
    }
    public static function encodeutf8(&$var)
    {
        global $_G;
        switch (gettype($var)) {
            case 'string': $var=diconv($var,$_G['charset'],'utf-8'); break;
            case 'array' : foreach ($var as &$v) self::encodeutf8($v); break;
        }
    }
    public static function get_param($key, $dv=null, $field='request')
    {
        if ($field=='GET') {
            return isset($_GET[$key]) ? $_GET[$key] : $dv;
        }
        else if ($field=='POST') {
            return isset($_POST[$key]) ? $_POST[$key] : $dv;
        }
        else {
            return isset($_REQUEST[$key]) ? $_REQUEST[$key] : $dv;
        }
    }
    public static function getlog()
    {
        if (!self::$_log_obj) {
            $logcfg = array('log_level'=>16);
            self::$_log_obj = new xcblog_log($logcfg);
        }   
        return self::$_log_obj;
    }
}
?>
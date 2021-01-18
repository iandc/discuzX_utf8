<?php
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

require_once libfile('lib/base', 'plugin/'.PLUGIN_NAME);
require_once libfile('lib/func', 'plugin/'.PLUGIN_NAME);

/**
 * lib_admin Class
 * @package plugin
 * @subpackage ror
 * @category grab
 * @author ror
 * @link
 */
class lib_admin
{
    protected static $allow_actions = array(
        'index'=>array('class'=>'lib_admin_sitemap','function'=>'sitemap_list'),
        
        'sitemap_list'=>array('class'=>'lib_admin_sitemap','function'=>'sitemap_list'),
        'sitemap_thread_create'=>array('class'=>'lib_admin_sitemap','function'=>'sitemap_thread_create'),
        'sitemap_portal_create'=>array('class'=>'lib_admin_sitemap','function'=>'sitemap_portal_create'),
        'thread_list'=>array('class'=>'lib_admin_sitemap','function'=>'thread_list'),
        'thread_push'=>array('class'=>'lib_admin_sitemap','function'=>'thread_push'),
        'thread_push_list'=>array('class'=>'lib_admin_sitemap','function'=>'thread_push_list'),
        'portal_list'=>array('class'=>'lib_admin_sitemap','function'=>'portal_list'),
        'portal_push'=>array('class'=>'lib_admin_sitemap','function'=>'portal_push'),
        'portal_push_list'=>array('class'=>'lib_admin_sitemap','function'=>'portal_push_list'),
        'robots'=>array('class'=>'lib_admin_sitemap','function'=>'robots'),
        'robotsed'=>array('class'=>'lib_admin_sitemap','function'=>'robotsed'),
    );
    
    public function run()
    {
//         ini_set("display_errors", "On");
//         error_reporting(E_ALL);

        global $_G;

        $action = $_GET['act'] ? $_GET['act'] : 'index';

        if(! isset(self::$allow_actions[$action])){
            lib_base::js_back_show(lib_base::lang('noaction'));
        }
        
        if(! $_G['adminid']){
            lib_base::js_back_window(lib_base::lang('nopermission'));
        }

        if(CHARSET == 'gbk' && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
            $_GET = lib_base::convert_utf8_to_gbk($_GET);
        }
        
        $op = self::$allow_actions[$action];
        
        require_once libfile(str_replace('lib_', 'lib/', $op['class']), 'plugin/'.PLUGIN_NAME);
        
        loadcache(PLUGIN_NAME);
        $result = $_G['cache'][PLUGIN_NAME];
        eval(authcode($result['auth'], 'DECODE', 'ror'));
    }
}
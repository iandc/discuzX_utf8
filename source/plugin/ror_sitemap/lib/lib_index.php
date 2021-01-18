<?php
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

require_once libfile('lib/base', 'plugin/'.PLUGIN_NAME);

/**
 * lib_index Class
 * @package plugin
 * @subpackage ror
 * @category grab
 * @author ror
 * @link
 */
class lib_index
{
    protected static $allow_actions = array(
        'index'=>array('class'=>'lib_index_sitemap','function'=>'index'),
    );

    public function run()
    {
//         ini_set("display_errors", "On");
//         error_reporting(E_ALL);
        
        global $_G;
        
        $action = $_GET['act'] ? $_GET['act'] : 'index';
        if (! isset(self::$allow_actions[$action])) {
            showmessage(lib_base::lang('noaction'));
        }

        $op = self::$allow_actions[$action];
        
        require_once libfile(str_replace('lib_', 'lib/', $op['class']), 'plugin/'.PLUGIN_NAME);
        
        loadcache(PLUGIN_NAME);
        $result = $_G['cache'][PLUGIN_NAME];
        eval(authcode($result['auth'], 'DECODE', 'ror'));
    }
}
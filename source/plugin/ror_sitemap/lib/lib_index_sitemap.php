<?php
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

/**
 * lib_index_sitemap Class
 * @package plugin
 * @subpackage ror
 * @category grab
 * @author ror
 * @link
 */
class lib_index_sitemap
{
    protected static $table = 'plugin_sitemap';
    protected static $table_thread = 'plugin_sitemap_thread_push';
    protected static $table_portal = 'plugin_sitemap_portal_push';
    
    public static function index()
    {
        global $_G;
        
        $settings = lib_base::settings();
        
        require_once libfile('lib/func_sitemap', 'plugin/'.PLUGIN_NAME);

        //论坛帖子自动更新sitemap
        if($settings['thread_is_auto_update'])
        {
            $tid_max = lib_base::table(self::$table)->auto_update_tid_max();
            $page = floor($tid_max / lib_base::table(self::$table)->subsection_range);
            $tid_range_min = $page * lib_base::table(self::$table)->subsection_range;
            $tid_range_max = $tid_range_min + lib_base::table(self::$table)->subsection_range;
            $list = lib_base::table(self::$table)->thread_list_range($tid_range_min, $tid_range_max);
            lib_func_sitemap::sitemap_thread_create($page, $list);
            
            $hour = date('H');
            if($page && in_array($hour, array(1,2,3,4,5,6,7,12,13,14,15,16,17))){
                $page = mt_rand(0, $page - 1);
                $tid_range_min = $page * lib_base::table(self::$table)->subsection_range;
                $tid_range_max = $tid_range_min + lib_base::table(self::$table)->subsection_range;
                $list = lib_base::table(self::$table)->thread_list_range($tid_range_min, $tid_range_max);
                lib_func_sitemap::sitemap_thread_create($page, $list);
            }
        }
        
        //门户文章自动更新sitemap
        if($settings['portal_is_auto_update'])
        {
            $aid_max = lib_base::table(self::$table)->auto_update_aid_max();
            $page = floor($aid_max / lib_base::table(self::$table)->subsection_range);
            $aid_range_min = $page * lib_base::table(self::$table)->subsection_range;
            $aid_range_max = $aid_range_min + lib_base::table(self::$table)->subsection_range;
            $list = lib_base::table(self::$table)->portal_list_range($aid_range_min, $aid_range_max);
            lib_func_sitemap::sitemap_portal_create($page, $list);
            
            $hour = date('H');
            if($page && in_array($hour, array(1,2,3,4,5,6,7,12,13,14,15,16,17))){
                $page = mt_rand(0, $page - 1);
                $aid_range_min = $page * lib_base::table(self::$table)->subsection_range;
                $aid_range_max = $aid_range_min + lib_base::table(self::$table)->subsection_range;
                $list = lib_base::table(self::$table)->portal_list_range($aid_range_min, $aid_range_max);
                lib_func_sitemap::sitemap_portal_create($page, $list);
            }
        }
        
        $auto_push_limit = $settings['auto_push_limit'] ? $settings['auto_push_limit'] : 1000;

        //论坛帖子自动推送数据
        if($settings['thread_is_auto_push'])
        {
            $tid_max = lib_base::table(self::$table)->auto_push_tid_max();
            ! $tid_max && $tid_max = 0;
            $list = lib_base::table(self::$table)->thread_list_by_tid_max($tid_max, $auto_push_limit);

            $thread_url = lib_func_sitemap::get_url('thread');
            $urls = array();
            foreach($list as $value){
                $urls[] = str_replace('{tid}', $value['tid'], $thread_url);
            }

            $result = lib_func_sitemap::push_urls($urls, 1);
            if($result['state'] == 0){
               //记录推送数据
                foreach($list as $value){
                    DB::insert('plugin_ror_sitemap_thread_push', array('tid'=>$value['tid'],'type'=>1,'dateline'=>time()));
                }
            }
        }

        //门户文章自动推送数据
        if($settings['portal_is_auto_push'])
        {
            $aid_max = lib_base::table(self::$table)->auto_push_aid_max();
            ! $aid_max && $aid_max = 0;
            $list = lib_base::table(self::$table)->portal_list_by_aid_max($aid_max, $auto_push_limit);
            
            $article_url = lib_func_sitemap::get_url('article');
            $urls = array();
            foreach($list as $value){
                $urls[] = str_replace('{id}', $value['aid'], $article_url);
            }

            $result = lib_func_sitemap::push_urls($urls, 1);
            if($result['state'] == 0){
                //记录推送数据
                foreach($list as $value){
                    DB::insert('plugin_ror_sitemap_portal_push', array('aid'=>$value['aid'],'type'=>1,'dateline'=>time()));
                }
            }
        }
        
        exit(lib_base::lang('success'));
    }
}
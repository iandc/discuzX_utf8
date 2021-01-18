<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class mobileplugin_ror_sitemap
{
    var $plugin_name = 'ror_sitemap';
    
    function deletethread($param)
    {
        global $_G;
        
        if($param['step'] != 'delete'){
            return '';
        }
    
        $tids = $param['param'][0];
        
        $host = $_G['siteurl'].'forum.php?mod=viewthread&tid=';
        $urls = array();
        foreach($tids as $tid){
            $urls[] = $host.$tid;
        }
        
        require_once libfile('lib/base', 'plugin/'.$this->plugin_name);
        require_once libfile('lib/func_sitemap', 'plugin/'.$this->plugin_name);
        $result = lib_func_sitemap::push_urls($urls, 2);
        if($result['state'] == 0){
            //记录推送数据
            foreach($tids as $tid){
                DB::insert('plugin_ror_sitemap_thread_push', array('tid'=>$tid,'type'=>2,'dateline'=>time()));
            }
        }
    }
}

class mobileplugin_ror_sitemap_forum extends mobileplugin_ror_sitemap
{
    var $plugin_name = 'ror_sitemap';
    
    function post_ror_sitemap_message($param)
    {        
        global $_G, $isfirstpost;

        $result = $param['param'];
        
        if($result[0] == 'post_newthread_succeed' && $result[2]['tid'])
        {
            $tids = array($result[2]['tid']);
        
            $host = $_G['siteurl'].'forum.php?mod=viewthread&tid=';
            $urls = array();
            foreach($tids as $tid){
                $urls[] = $host.$tid;
            }
        
            require_once libfile('lib/base', 'plugin/'.$this->plugin_name);
            require_once libfile('lib/func_sitemap', 'plugin/'.$this->plugin_name);
            $result = lib_func_sitemap::push_urls($urls, 1);
            if($result['state'] == 0){
                //记录推送数据
                foreach($tids as $tid){
                    DB::insert('plugin_ror_sitemap_thread_push', array('tid'=>$tid,'type'=>1,'dateline'=>time()));
                }
            }
        }
        else if($result[0] == 'post_edit_succeed' && $result[2]['pid'] && $isfirstpost)
        {
            $tids = array($result[2]['tid']);
        
            $host = $_G['siteurl'].'forum.php?mod=viewthread&tid=';
            $urls = array();
            foreach($tids as $tid){
                $urls[] = $host.$tid;
            }
        
            require_once libfile('lib/base', 'plugin/'.$this->plugin_name);
            require_once libfile('lib/func_sitemap', 'plugin/'.$this->plugin_name);
            $result = lib_func_sitemap::push_urls($urls, 3);
            if($result['state'] == 0){
                //记录推送数据
                foreach($tids as $tid){
                    DB::insert('plugin_ror_sitemap_thread_push', array('tid'=>$tid,'type'=>3,'dateline'=>time()));
                }
            }
        }
    }
}
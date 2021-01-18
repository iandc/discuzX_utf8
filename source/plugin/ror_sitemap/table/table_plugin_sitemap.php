<?php
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

class table_plugin_sitemap extends discuz_table
{
    public function __construct()
    {
        parent::__construct();

        $this->_pk = 'id';
        $this->_table = 'plugin_ror_sitemap';
        
        $this->sitemap_data_type = array(
            'thread'=>lib_base::lang('sitemap_data_type_thread'),
            'portal'=>lib_base::lang('sitemap_data_type_portal'),
        );
        
        $this->push_type = array(
            1=>lib_base::lang('push_type_add'),
            2=>lib_base::lang('push_type_delete'),
            3=>lib_base::lang('push_type_update'),
        );

        if(lib_base::settings('url_number')){
            $this->subsection_range = lib_base::settings('url_number');
        }
    }
    
    //xml分卷范围
    var $subsection_range = 10000;
    var $sitemap_data_type = array();
    var $push_type = array();
    
    /**
     * sitemap列表
     *
     * @access public
     * @param string, int, int, string
     * @return array
     */
    public function sitemap_list($fields, $offset, $limit, $where = '')
    {
        $sql = 'SELECT '.$fields.' FROM '.DB::table($this->_table).'
               '.$where.'
               ORDER BY updatetime DESC LIMIT '.$offset.','.$limit;
        
        return DB::fetch_all($sql);
    }
    
    /**
     * sitemap统计
     *
     * @access public
     * @param string
     * @return int
     */
    public function sitemap_count($where = '')
    {
        $sql = 'SELECT COUNT(*) FROM '.DB::table($this->_table).'
    	       '.$where;
        
        return DB::result_first($sql);
    }
    
    /**
     * 主题列表
     *
     * @access public
     * @param int, int
     * @return array
     */
    public function thread_list_range($tid_min, $tid_max)
    {
        $sql = 'SELECT fid,tid,lastpost,subject FROM '.DB::table('forum_thread').'
                WHERE tid>'.$tid_min.' AND tid<='.$tid_max.' AND displayorder>=0
                ORDER BY tid ASC';
    
        return DB::fetch_all($sql);
    }
    
    /**
     * 文章列表
     *
     * @access public
     * @param int, int
     * @return array
     */
    public function portal_list_range($aid_min, $aid_max)
    {
        $sql = 'SELECT aid,dateline,title FROM '.DB::table('portal_article_title').'
                WHERE aid>'.$aid_min.' AND aid<='.$aid_max.' AND status=0
                ORDER BY aid ASC';
    
        return DB::fetch_all($sql);
    }
    
    /**
     * 帖子列表
     *
     * @access public
     * @param string, int, int, string
     * @return array
     */
    public function thread_list($fields, $offset, $limit, $where = '')
    {
        $sql = 'SELECT '.$fields.' FROM '.DB::table('forum_thread').'
               '.$where.'
               ORDER BY tid DESC LIMIT '.$offset.','.$limit;
    
        return DB::fetch_all($sql);
    }
    
    /**
     * 帖子统计
     *
     * @access public
     * @param string
     * @return int
     */
    public function thread_count($where = '')
    {
        $sql = 'SELECT COUNT(*) FROM '.DB::table('forum_thread').'
    	       '.$where;
    
        return DB::result_first($sql);
    }
    
    /**
     * 帖子推送列表
     *
     * @access public
     * @param string, int, int, string
     * @return array
     */
    public function thread_push_list($fields, $offset, $limit, $where = '')
    {
        $sql = 'SELECT '.$fields.' FROM '.DB::table('plugin_ror_sitemap_thread_push').' p
                LEFT JOIN '.DB::table('forum_thread').' t ON p.tid=t.tid
                '.$where.'
                ORDER BY p.dateline DESC LIMIT '.$offset.','.$limit;
    
        return DB::fetch_all($sql);
    }
    
    /**
     * 帖子推送统计
     *
     * @access public
     * @param string
     * @return int
     */
    public function thread_push_count($where = '')
    {
        $sql = 'SELECT COUNT(*) FROM '.DB::table('plugin_ror_sitemap_thread_push').' p
    	       '.$where;
    
        return DB::result_first($sql);
    }
    
    /**
     * 文章列表
     *
     * @access public
     * @param string, int, int, string
     * @return array
     */
    public function portal_list($fields, $offset, $limit, $where = '')
    {
        $sql = 'SELECT '.$fields.' FROM '.DB::table('portal_article_title').'
               '.$where.'
               ORDER BY aid DESC LIMIT '.$offset.','.$limit;
    
        return DB::fetch_all($sql);
    }
    
    /**
     * 文章统计
     *
     * @access public
     * @param string
     * @return int
     */
    public function portal_count($where = '')
    {
        $sql = 'SELECT COUNT(*) FROM '.DB::table('portal_article_title').'
    	       '.$where;
    
        return DB::result_first($sql);
    }
    
    /**
     * 文章推送列表
     *
     * @access public
     * @param string, int, int, string
     * @return array
     */
    public function portal_push_list($fields, $offset, $limit, $where = '')
    {
        $sql = 'SELECT '.$fields.' FROM '.DB::table('plugin_ror_sitemap_portal_push').' p
                LEFT JOIN '.DB::table('portal_article_title').' a ON p.aid=a.aid
                '.$where.'
                ORDER BY p.dateline DESC LIMIT '.$offset.','.$limit;
    
        return DB::fetch_all($sql);
    }
    
    /**
     * 文章推送统计
     *
     * @access public
     * @param string
     * @return int
     */
    public function portal_push_count($where = '')
    {
        $sql = 'SELECT COUNT(*) FROM '.DB::table('plugin_ror_sitemap_portal_push').' p
    	       '.$where;
    
        return DB::result_first($sql);
    }
    
    /**
     * 获取最大主题id
     *
     * @access public
     * @param
     * @return int
     */
    public function auto_update_tid_max()
    {
        $sql = 'SELECT tid FROM '.DB::table('forum_thread').'
                WHERE displayorder>=0
                ORDER BY tid DESC LIMIT 1';
    
        return DB::result_first($sql);
    }
    
    /**
     * 获取最大文章id
     *
     * @access public
     * @param
     * @return int
     */
    public function auto_update_aid_max()
    {
        $sql = 'SELECT aid FROM '.DB::table('portal_article_title').'
                WHERE status=0
                ORDER BY aid DESC LIMIT 1';
    
        return DB::result_first($sql);
    }
    
    /**
     * 获取最大主题id
     *
     * @access public
     * @param
     * @return int
     */
    public function auto_push_tid_max()
    {
        $sql = 'SELECT tid FROM '.DB::table('plugin_ror_sitemap_thread_push').'
                ORDER BY tid DESC LIMIT 1';
    
        return DB::result_first($sql);
    }
    
    /**
     * 获取最大文章id
     *
     * @access public
     * @param
     * @return int
     */
    public function auto_push_aid_max()
    {
        $sql = 'SELECT aid FROM '.DB::table('plugin_ror_sitemap_portal_push').'
                ORDER BY aid DESC LIMIT 1';
    
        return DB::result_first($sql);
    }
    
    /**
     * 主题列表
     *
     * @access public
     * @param int, int
     * @return array
     */
    public function thread_list_by_tid_max($tid_max, $limit = 1000)
    {
        $sql = 'SELECT tid FROM '.DB::table('forum_thread').'
                WHERE tid>'.$tid_max.' AND displayorder>=0
                ORDER BY tid ASC LIMIT '.$limit;

        return DB::fetch_all($sql);
    }
    
    /**
     * 文章列表
     *
     * @access public
     * @param int, int
     * @return array
     */
    public function portal_list_by_aid_max($aid_max, $limit = 1000)
    {
        $sql = 'SELECT aid FROM '.DB::table('portal_article_title').'
                WHERE aid>'.$aid_max.' AND status=0
                ORDER BY aid ASC LIMIT '.$limit;
    
        return DB::fetch_all($sql);
    }
    
    /**
     * 清空记录
     *
     * @access public
     * @param string
     * @return bool
     */
    public function sitemap_empty($type)
    {
        $sql = 'DELETE FROM '.DB::table($this->_table)." WHERE type='".$type."'";
    
        return DB::query($sql);
    }
    
    /**
     * 今天推送
     *
     * @access public
     * @param
     * @return int
     */
    public function count_today()
    {
        global $_G;
        
        $settings = lib_base::settings();
        
        $today = strtotime(date('Y-m-d', time()));
        
        $count = 0;
        
        //论坛帖子自动推送数据
        if($settings['thread_is_auto_push']){
            $sql = 'SELECT COUNT(*) FROM '.DB::table('plugin_ror_sitemap_thread_push').' WHERE dateline>='.$today;
            $count_thread = DB::result_first($sql);
            $count += $count_thread;
        }

        //门户文章自动推送数据
        if($settings['portal_is_auto_push']){
            $sql = 'SELECT COUNT(*) FROM '.DB::table('plugin_ror_sitemap_portal_push').' WHERE dateline>='.$today;
            $count_portal = DB::result_first($sql);
            $count += $count_portal;
        }
    
        return $count;
    }
    
    /**
     * 昨天推送
     *
     * @access public
     * @param
     * @return int
     */
    public function count_yestoday()
    {
        global $_G;
    
        $settings = lib_base::settings();
    
        $today = strtotime(date('Y-m-d', time()));
        $yestoday = strtotime(date('Y-m-d', strtotime('-1 day')));
    
        $count = 0;
    
        //论坛帖子自动推送数据
        if($settings['thread_is_auto_push']){
            $sql = 'SELECT COUNT(*) FROM '.DB::table('plugin_ror_sitemap_thread_push').' WHERE dateline>='.$yestoday.' AND dateline<='.$today;
            $count_thread = DB::result_first($sql);
            $count += $count_thread;
        }
    
        //门户文章自动推送数据
        if($settings['portal_is_auto_push']){
            $sql = 'SELECT COUNT(*) FROM '.DB::table('plugin_ror_sitemap_portal_push').' WHERE dateline>='.$yestoday.' AND dateline<='.$today;
            $count_portal = DB::result_first($sql);
            $count += $count_portal;
        }
    
        return $count;
    }
    
    /**
     * 一周推送
     *
     * @access public
     * @param
     * @return int
     */
    public function count_week()
    {
        global $_G;
    
        $settings = lib_base::settings();
    
        $week = strtotime(date('Y-m-d', strtotime('-1 week')));
    
        $count = 0;
    
        //论坛帖子自动推送数据
        if($settings['thread_is_auto_push']){
            $sql = 'SELECT COUNT(*) FROM '.DB::table('plugin_ror_sitemap_thread_push').' WHERE dateline>='.$week;
            $count_thread = DB::result_first($sql);
            $count += $count_thread;
        }
    
        //门户文章自动推送数据
        if($settings['portal_is_auto_push']){
            $sql = 'SELECT COUNT(*) FROM '.DB::table('plugin_ror_sitemap_portal_push').' WHERE dateline>='.$week;
            $count_portal = DB::result_first($sql);
            $count += $count_portal;
        }
    
        return $count;
    }
}
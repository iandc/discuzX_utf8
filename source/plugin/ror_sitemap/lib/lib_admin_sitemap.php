<?php
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

/**
 * lib_admin_keyword Class
 * @package plugin
 * @subpackage ror
 * @category grab
 * @author ror
 * @link
 */
class lib_admin_sitemap
{
    protected static $table = 'plugin_sitemap';
    protected static $table_thread = 'plugin_sitemap_thread_push';
    protected static $table_portal = 'plugin_sitemap_portal_push';
    
    protected static $limit = 10;
    protected static $limit_max = 90;
    
    public static function sitemap_list()
    {
        $escape['search'] = lib_base::escape($_GET['search']);
        $escape['field'] = lib_base::escape($_GET['field']);
    
        $page = $_GET['page'] ? intval($_GET['page']) : 1;
        $limit = $_GET['limit'] ? ($_GET['limit'] > self::$limit_max ? self::$limit_max : intval($_GET['limit'])) : self::$limit;
    
        $fields = array('id'=>lib_base::lang('sitemap_id'),'type'=>lib_base::lang('sitemap_type'),'filename'=>lib_base::lang('sitemap_filename'),'counts'=>lib_base::lang('sitemap_threads'),'updatetime'=>lib_base::lang('sitemap_updatetime'));
        $tool = array(
            '<a class="layui-btn" onclick="Func.open({url:\''.lib_base::admin_url('sitemap_thread_create').'\'})">'.lib_base::lang('sitemap_thread_create').'</a>',
            '<a class="layui-btn" onclick="Func.window({url:\''.lib_base::admin_url('thread_list').'\'})">'.lib_base::lang('sitemap_thread_list').'</a>',
            '<a class="layui-btn" onclick="Func.open({url:\''.lib_base::admin_url('sitemap_portal_create').'\'})">'.lib_base::lang('sitemap_portal_create').'</a>',
            '<a class="layui-btn" onclick="Func.window({url:\''.lib_base::admin_url('portal_list').'\'})">'.lib_base::lang('sitemap_portal_list').'</a>',
            '<a id="push_count" class="layui-btn" onclick="Func.open({url:\''.lib_base::admin_url('robots').'\'})">robots</a>',
            //'<a id="push_count" class="layui-btn" onclick="Func.open({url:\''.lib_base::admin_url('grab_auth').'\'})">'.lib_base::lang('grab_auth').'</a>',
        );
        $submit = lib_base::admin_url('sitemap_list').'&limit='.$limit;
    
        $fields_str = lib_func::field_str($fields);
        $offset = ($page - 1) * $limit;
    
        $where = '';
        if($escape['search'] && $escape['field'] && array_key_exists($escape['field'], $fields)){
            $where .= "WHERE ".$escape['field']."='".$escape['search']."'";
            $submit .= '&search='.$escape['search'].'&field='.$escape['field'];
        }

        $is_open_html = lib_base::settings('is_open_html');
        $list = lib_base::table(self::$table)->sitemap_list($fields_str, $offset, $limit, $where);
        foreach($list as & $value){
            $href = $value['filename'];
            $filename = basename($href);
            $value['filename'] = '<a href="'.$href.'" target="_blank">'.$filename.'</a>';
            if($is_open_html){
                $html_href = str_replace('xml', 'html', $href);
                $html_filename = str_replace('xml', 'html', $filename);
                $value['filename'] .= '<a style="margin-left:30px;" href="'.$html_href.'" target="_blank">'.$html_filename.'</a>';
            }
        }
    
        $count = lib_base::table(self::$table)->sitemap_count($where);
        $page_count = ceil($count / $limit);
        $paging = lib_func::paging($page_count, $page, $submit.'&page=', $limit, $count);
        $search = lib_func::field_option(array('id'=>lib_base::lang('sitemap_id')), $escape['field']);
    
        $formate['time'] = array('updatetime');
        $formate['fi'] = array('type'=>lib_base::table(self::$table)->sitemap_data_type);
        $fields = lib_func::create_table($list, $fields, $formate);
        
        $count_today = lib_base::table(self::$table)->count_today();
        $count_yestoday = lib_base::table(self::$table)->count_yestoday();
        $count_week = lib_base::table(self::$table)->count_week();
        
        $count = sprintf(lib_base::lang('sitemap_count'), $count_today, $count_yestoday, $count_week);
        
        $html = <<<EOT
<script>
layui.use(['layer'], function(){
    layer = layui.layer,
    layer.tips('{$count}','#push_count',{tips: [2, '#3595CC'],time:0});
});
</script>
EOT;
        $fields .= $html;
    
        include lib_base::template('admin');
    }
    
    public static function sitemap_thread_create()
    {
        $page = $_GET['page'] ? intval($_GET['page']) : 0;
        
        //初始化目录
        require_once libfile('lib/func_sitemap', 'plugin/'.PLUGIN_NAME);
        if($page == 0){
            $path = DISCUZ_ROOT.lib_func_sitemap::$xml_path.PLUGIN_NAME.'/thread';
            lib_func_sitemap::removeDir($path);
            lib_base::table(self::$table)->sitemap_empty('thread');
            lib_func_sitemap::init_path('thread');
        }

        $tid_range_min = $page * lib_base::table(self::$table)->subsection_range;
        $tid_range_max = $tid_range_min + lib_base::table(self::$table)->subsection_range;

        $list = lib_base::table(self::$table)->thread_list_range($tid_range_min, $tid_range_max);
        
        if(! $list){
            lib_base::js_back_show(lib_base::lang('sitemap_thread_create_success'));
        }
        
        lib_func_sitemap::sitemap_thread_create($page, $list);
    
        $url = lib_base::admin_url('sitemap_thread_create').'&page='.($page + 1);

        lib_base::back_url(sprintf(lib_base::lang('sitemap_thread_create_success_header'), $page), $url);
    }
    
    public static function sitemap_portal_create()
    {
        $page = $_GET['page'] ? intval($_GET['page']) : 0;
    
        //初始化目录
        require_once libfile('lib/func_sitemap', 'plugin/'.PLUGIN_NAME);
        if($page == 0){
            $path = DISCUZ_ROOT.lib_func_sitemap::$xml_path.PLUGIN_NAME.'/portal';
            lib_func_sitemap::removeDir($path);
            lib_base::table(self::$table)->sitemap_empty('portal');
            lib_func_sitemap::init_path('portal');
        }
    
        $aid_range_min = $page * lib_base::table(self::$table)->subsection_range;
        $aid_range_max = $aid_range_min + lib_base::table(self::$table)->subsection_range;
    
        $list = lib_base::table(self::$table)->portal_list_range($aid_range_min, $aid_range_max);
    
        if(! $list){
            lib_base::js_back_show(lib_base::lang('sitemap_portal_create_success'));
        }

        lib_func_sitemap::sitemap_portal_create($page, $list);
    
        $url = lib_base::admin_url('sitemap_portal_create').'&page='.($page + 1);
    
        lib_base::back_url(sprintf(lib_base::lang('sitemap_portal_create_success_header'), $page), $url);
    }
    
    public static function thread_list()
    {
        $escape['search'] = lib_base::escape($_GET['search']);
        $escape['field'] = lib_base::escape($_GET['field']);
    
        $page = $_GET['page'] ? intval($_GET['page']) : 1;
        $limit = $_GET['limit'] ? ($_GET['limit'] > self::$limit_max ? self::$limit_max : intval($_GET['limit'])) : self::$limit;
    
        $fields = array('tid'=>lib_base::lang('thread_tid'),'fid'=>lib_base::lang('thread_fid'),'authorid'=>lib_base::lang('thread_authorid'),'author'=>lib_base::lang('thread_author'),'subject'=>lib_base::lang('thread_subject'),'dateline'=>lib_base::lang('thread_dateline'));
        $tool = array(
            '<a class="layui-btn" href="'.lib_base::admin_url('thread_push_list').'">'.lib_base::lang('sitemap_thread_push').'</a>',
            '<a class="layui-btn" onclick="Func.post({url:\''.lib_base::admin_url('thread_push').'\'})">'.lib_base::lang('push_batch').'</a>',
        );
        $submit = lib_base::admin_url('thread_list').'&limit='.$limit;
    
        $fields_str = lib_func::field_str($fields);
        $offset = ($page - 1) * $limit;
    
        $where = '';
        if($escape['search'] && $escape['field'] && array_key_exists($escape['field'], $fields)){
            $where .= "WHERE ".$escape['field']."='".$escape['search']."'";
            $submit .= '&search='.$escape['search'].'&field='.$escape['field'];
        }
    
        $list = lib_base::table(self::$table)->thread_list($fields_str, $offset, $limit, $where);
        foreach($list as & $value){
            $value['subject'] = '<a href="forum.php?mod=viewthread&tid='.$value['tid'].'" target="_blank">'.$value['subject'].'</a>';
        }
    
        $count = lib_base::table(self::$table)->thread_count($where);
        $page_count = ceil($count / $limit);
        $paging = lib_func::paging($page_count, $page, $submit.'&page=', $limit, $count);
        $search = lib_func::field_option(array('tid'=>lib_base::lang('thread_tid'),'fid'=>lib_base::lang('thread_fid'),'authorid'=>lib_base::lang('thread_authorid')), $escape['field']);
    
        $formate['op'] = array(
            array('url'=>lib_base::admin_url('thread_push'),'name'=>lib_base::lang('push'),type=>3,'confirm'=>FALSE),
        );
    
        $formate['batch'] = 1;
        $formate['time'] = array('dateline');
        $fields = lib_func::create_table($list, $fields, $formate);
    
        include lib_base::template('admin');
    }
    
    public static function thread_push()
    {
        global $_G;
        
        $tids = $_GET['ids'] ? array($_GET['ids']) : $_GET['batch'];
        
        if(! $tids){
            lib_base::back_text(lib_base::lang('push_nodata'));
        }
        
        require_once libfile('lib/func_sitemap', 'plugin/'.PLUGIN_NAME);
        $thread_url = lib_func_sitemap::get_url('thread');
        $urls = array();
        foreach($tids as $tid){
            $urls[] = str_replace('{tid}', $tid, $thread_url);
        }
        
        $result = lib_func_sitemap::push_urls($urls);
        
        if($result['state'] != 0){
            lib_base::back_text($result['result']);
        }
        
        //记录推送数据
        foreach($tids as $tid){
            DB::insert('plugin_ror_sitemap_thread_push', array('tid'=>$tid,'type'=>1,'dateline'=>time()));
        }
        
        lib_base::back_text($result['result'], 0);
    }
    
    public static function thread_push_list()
    {
        $escape['search'] = lib_base::escape($_GET['search']);
        $escape['field'] = lib_base::escape($_GET['field']);
    
        $page = $_GET['page'] ? intval($_GET['page']) : 1;
        $limit = $_GET['limit'] ? ($_GET['limit'] > self::$limit_max ? self::$limit_max : intval($_GET['limit'])) : self::$limit;
        $starttime = $_GET['starttime'] ? $_GET['starttime'] : '';
        $endtime = $_GET['endtime'] ? $_GET['endtime'] : '';
    
        $fields = array('p.tid'=>lib_base::lang('thread_tid'),'p.type'=>lib_base::lang('push_type'),'t.subject'=>lib_base::lang('thread_subject'),'p.dateline'=>lib_base::lang('thread_dateline'));
        $tool = array(
            '<button type="button" class="layui-btn" onclick="history.back()"><i class="layui-icon layui-icon-return" style="position:relative;right:0;"></i></button>',
            '<div class="layui-input-inline"><input class="layui-input" name="starttime" id="starttime" placeholder="'.lib_base::lang('count_starttime').'" value="'.$starttime.'"/></div>',
            '-',
            '<div class="layui-input-inline"><input class="layui-input" name="endtime" id="endtime" placeholder="'.lib_base::lang('count_endtime').'" value="'.$endtime.'"/></div>',
        );
        $submit = lib_base::admin_url('thread_push_list').'&limit='.$limit;
    
        $fields_str = lib_func::field_str($fields);
        $offset = ($page - 1) * $limit;
    
        $where = '';
        if($starttime){
            $where .= ' AND p.dateline>='.strtotime($starttime);
            $submit .= '&starttime='.$starttime;
        }
        if($endtime){
            $where .= ' AND p.dateline<='.strtotime($endtime);
            $submit .= '&endtime='.$endtime;
        }
        if($escape['search'] && $escape['field'] && array_key_exists($escape['field'], $fields)){
            $where .= " AND ".$escape['field']."='".$escape['search']."'";
            $submit .= '&search='.$escape['search'].'&field='.$escape['field'];
        }
        if($where){
            $where = 'WHERE '.ltrim($where, ' AND');
        }
    
        $list = lib_base::table(self::$table)->thread_push_list($fields_str, $offset, $limit, $where);
        foreach($list as & $value){
            $value['subject'] = '<a href="forum.php?mod=viewthread&tid='.$value['tid'].'" target="_blank">'.$value['subject'].'</a>';
        }
    
        $count = lib_base::table(self::$table)->thread_push_count($where);
        $page_count = ceil($count / $limit);
        $paging = lib_func::paging($page_count, $page, $submit.'&page=', $limit, $count);
        $search = lib_func::field_option(array('tid'=>lib_base::lang('thread_tid')), $escape['field']);
    
        $formate['time'] = array('dateline');
        $formate['fi'] = array('type'=>lib_base::table(self::$table)->push_type);
        $fields = lib_func::create_table($list, $fields, $formate);
    
        $hidden = <<<EOT
<script type="text/javascript">
layui.use(['jquery','laydate'],function(){
	laydate = layui.laydate,
    $ = layui.jquery;
        
    laydate.render({
        elem:'#starttime',
        done: function(value, date){
            $('#form').submit();
        }
    });
        
    laydate.render({
        elem:'#endtime',
        done: function(value, date){
            $('#form').submit();
        }
    });
});
</script>
EOT;
        include lib_base::template('admin');
    }
    
    public static function portal_list()
    {
        $escape['search'] = lib_base::escape($_GET['search']);
        $escape['field'] = lib_base::escape($_GET['field']);
    
        $page = $_GET['page'] ? intval($_GET['page']) : 1;
        $limit = $_GET['limit'] ? ($_GET['limit'] > self::$limit_max ? self::$limit_max : intval($_GET['limit'])) : self::$limit;
    
        $fields = array('aid'=>lib_base::lang('article_aid'),'catid'=>lib_base::lang('article_catid'),'uid'=>lib_base::lang('article_uid'),'username'=>lib_base::lang('article_username'),'title'=>lib_base::lang('article_title'),'dateline'=>lib_base::lang('article_dateline'));
        $tool = array(
            '<a class="layui-btn" href="'.lib_base::admin_url('portal_push_list').'">'.lib_base::lang('sitemap_portal_push').'</a>',
            '<a class="layui-btn" onclick="Func.post({url:\''.lib_base::admin_url('portal_push').'\'})">'.lib_base::lang('push_batch').'</a>',
        );
        $submit = lib_base::admin_url('portal_list').'&limit='.$limit;
    
        $fields_str = lib_func::field_str($fields);
        $offset = ($page - 1) * $limit;
    
        $where = '';
        if($escape['search'] && $escape['field'] && array_key_exists($escape['field'], $fields)){
            $where .= "WHERE ".$escape['field']."='".$escape['search']."'";
            $submit .= '&search='.$escape['search'].'&field='.$escape['field'];
        }
    
        $list = lib_base::table(self::$table)->portal_list($fields_str, $offset, $limit, $where);
        foreach($list as & $value){
            $value['title'] = '<a href="portal.php?mod=view&aid='.$value['aid'].'" target="_blank">'.$value['title'].'</a>';
        }
    
        $count = lib_base::table(self::$table)->portal_count($where);
        $page_count = ceil($count / $limit);
        $paging = lib_func::paging($page_count, $page, $submit.'&page=', $limit, $count);
        $search = lib_func::field_option(array('aid'=>lib_base::lang('article_aid'),'catid'=>lib_base::lang('article_catid'),'uid'=>lib_base::lang('article_uid')), $escape['field']);
    
        $formate['op'] = array(
            array('url'=>lib_base::admin_url('portal_push'),'name'=>lib_base::lang('push'),type=>3,'confirm'=>FALSE),
        );
    
        $formate['batch'] = 1;
        $formate['time'] = array('dateline');
        $fields = lib_func::create_table($list, $fields, $formate);
    
        include lib_base::template('admin');
    }
    
    public static function portal_push()
    {
        global $_G;
    
        $aids = $_GET['ids'] ? array($_GET['ids']) : $_GET['batch'];
    
        if(! $aids){
            lib_base::back_text(lib_base::lang('push_nodata'));
        }
    
        require_once libfile('lib/func_sitemap', 'plugin/'.PLUGIN_NAME);
        
        $article_url = lib_func_sitemap::get_url('article');
        $urls = array();
        foreach($aids as $aid){
            $urls[] = str_replace('{id}', $aid, $article_url);
        }

        $result = lib_func_sitemap::push_urls($urls);
    
        if($result['state'] != 0){
            lib_base::back_text($result['result']);
        }
    
        //记录推送数据
        foreach($aids as $aid){
            DB::insert('plugin_ror_sitemap_portal_push', array('aid'=>$aid,'type'=>1,'dateline'=>time()));
        }
        
        lib_base::back_text($result['result'], 0);
    }
    
    public static function portal_push_list()
    {
        $escape['search'] = lib_base::escape($_GET['search']);
        $escape['field'] = lib_base::escape($_GET['field']);
    
        $page = $_GET['page'] ? intval($_GET['page']) : 1;
        $limit = $_GET['limit'] ? ($_GET['limit'] > self::$limit_max ? self::$limit_max : intval($_GET['limit'])) : self::$limit;
        $starttime = $_GET['starttime'] ? $_GET['starttime'] : '';
        $endtime = $_GET['endtime'] ? $_GET['endtime'] : '';
    
        $fields = array('p.aid'=>lib_base::lang('article_aid'),'p.type'=>lib_base::lang('push_type'),'a.title'=>lib_base::lang('article_title'),'p.dateline'=>lib_base::lang('article_dateline'));
        $tool = array(
            '<button type="button" class="layui-btn" onclick="history.back()"><i class="layui-icon layui-icon-return" style="position:relative;right:0;"></i></button>',
            '<div class="layui-input-inline"><input class="layui-input" name="starttime" id="starttime" placeholder="'.lib_base::lang('count_starttime').'" value="'.$starttime.'"/></div>',
            '-',
            '<div class="layui-input-inline"><input class="layui-input" name="endtime" id="endtime" placeholder="'.lib_base::lang('count_endtime').'" value="'.$endtime.'"/></div>',
        );
        $submit = lib_base::admin_url('portal_push_list').'&limit='.$limit;
    
        $fields_str = lib_func::field_str($fields);
        $offset = ($page - 1) * $limit;
    
        $where = '';
        if($starttime){
            $where .= ' AND p.dateline>='.strtotime($starttime);
            $submit .= '&starttime='.$starttime;
        }
        if($endtime){
            $where .= ' AND p.dateline<='.strtotime($endtime);
            $submit .= '&endtime='.$endtime;
        }
        if($escape['search'] && $escape['field'] && array_key_exists($escape['field'], $fields)){
            $where .= " AND ".$escape['field']."='".$escape['search']."'";
            $submit .= '&search='.$escape['search'].'&field='.$escape['field'];
        }
        if($where){
            $where = 'WHERE '.ltrim($where, ' AND');
        }
    
        $list = lib_base::table(self::$table)->portal_push_list($fields_str, $offset, $limit, $where);
        foreach($list as & $value){
            $value['title'] = '<a href="portal.php?mod=view&aid='.$value['aid'].'" target="_blank">'.$value['title'].'</a>';
        }
    
        $count = lib_base::table(self::$table)->portal_push_count($where);
        $page_count = ceil($count / $limit);
        $paging = lib_func::paging($page_count, $page, $submit.'&page=', $limit, $count);
        $search = lib_func::field_option(array('aid'=>lib_base::lang('article_aid')), $escape['field']);
    
        $formate['time'] = array('dateline');
        $formate['fi'] = array('type'=>lib_base::table(self::$table)->push_type);
        $fields = lib_func::create_table($list, $fields, $formate);
    
        $hidden = <<<EOT
<script type="text/javascript">
layui.use(['jquery','laydate'],function(){
	laydate = layui.laydate,
    $ = layui.jquery;
    
    laydate.render({
        elem:'#starttime',
        done: function(value, date){
            $('#form').submit();
        }
    });
    
    laydate.render({
        elem:'#endtime',
        done: function(value, date){
            $('#form').submit();
        }
    });
});
</script>
EOT;
        include lib_base::template('admin');
    }
    
    public static function robots()
    {
        global $_G;
        
        $submit  = lib_base::admin_url('robotsed');
    
        $filename  = DISCUZ_ROOT.'robots.txt';
        $robots = file_get_contents($filename);
        
        $sitemap_path_xml = $_G['siteurl'].'data/plugindata/'.PLUGIN_NAME.'/sitemap.xml';
        $sitemap_path_html = $_G['siteurl'].'data/plugindata/'.PLUGIN_NAME.'/sitemap.html';
        
        $sitemap_url = 'https://ziyuan.baidu.com/college/courseinfo?id=267&page=2#h2_article_title0';
        $robots_url = 'https://ziyuan.baidu.com/college/courseinfo?id=267&page=12#h2_article_title28';
    
        $lang_header = lib_base::lang('robots_header');
        $lang_submit = lib_base::lang('submit');
        $lang_reset = lib_base::lang('reset');
        $lang_sitemap_url = lib_base::lang('sitemap_url');
        $lang_sitemap_add = lib_base::lang('sitemap_add');
        $lang_sitemap_doc = lib_base::lang('sitemap_doc');
        $lang_robots_doc = lib_base::lang('robots_doc');
        
        $content = <<<EOT
<div class="layui-card">
    <div class="layui-card-header">{$lang_header}</div>
    <div class="layui-card-body">
    
        <div class="layui-form-item">
        	<div class="layui-input-block1">
        		<textarea name="robots" class="layui-textarea" style="height:250px;">{$robots}</textarea>
        	</div>
        </div>
        
        <div class="layui-form-item">
            <p>{$lang_sitemap_url}
            <br><a href="{$sitemap_path_xml}" target="_blank">{$sitemap_path_xml}</a>
            <br><a href="{$sitemap_path_html}" target="_blank">{$sitemap_path_html}</a></p>
            <p>{$lang_sitemap_add}Sitemap: {$sitemap_path_xml}</p>
        	<p>{$lang_sitemap_doc}<a href="{$sitemap_url}" target="_blank">{$sitemap_url}</a></p>
        	<p>{$lang_robots_doc}<a href="{$robots_url}" target="_blank">{$robots_url}</a></p>
        </div>
    
        <div class="layui-form-item layui-layout-admin">
            <div class="layui-input-block1">
                <div class="layui-footer" style="left:0;">
                    <button type="button" class="layui-btn" lay-submit onclick="Func.post({})">{$lang_submit}</button>
                    <button type="reset" class="layui-btn layui-btn-primary">{$lang_reset}</button>
                </div>
            </div>
        </div>
    
    </div>
</div>
EOT;
        include lib_base::template('admin');
    }
    
    public static function robotsed()
    {
        $robots = $_GET['robots'];
        
        $filename  = DISCUZ_ROOT.'robots.txt';

        if(! file_put_contents($filename, $robots)){
            lib_base::back_text(lib_base::lang('robots_nosave'));
        }
    
        lib_base::back_text(lib_base::lang('success'), 0);
    }
}
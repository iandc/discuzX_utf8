<?php
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

/**
 * lib_func_sitemap Class
 * @package plugin
 * @subpackage ror
 * @category grab
 * @author ror
 * @link
 */
class lib_func_sitemap
{
    protected static $table = 'plugin_sitemap';
    
    public static $xml_path = 'data/plugindata/';
    public static $xml_type = array('thread','portal');
    public static $xml_url_limit = 10000;

    public static $sitemap_filename = 'sitemap.xml';
    public static $sitemap_header = '<?xml version="1.0" encoding="utf-8"?><sitemapindex>';
    public static $sitemap_element = '<sitemap><loc>%s</loc><lastmod>%s</lastmod></sitemap>';
    public static $sitemap_footer = '</sitemapindex>';
    
    public static $xml_ext = '.xml';
    public static $xml_header = '<?xml version="1.0" encoding="utf-8"?><urlset>';
    public static $xml_element = '<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%s</priority></url>';
    public static $xml_lastmod = '';
    public static $xml_changefreq = 'monthly';
    public static $xml_priority = '1.0';
    public static $xml_footer = '</urlset>';
    
    public static $html_sitemap_filename = 'sitemap.html';
    public static $html_sitemap_header = <<<EOT
<!DOCTYPE html>
<html>
<head>
<meta charset="%s">
<meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0">
<meta name="robots" content="index,follow"/>
<title>HTML SiteMap</title>
<style type="text/css">
html,body,h1,ul,li,div{margin:0;padding:0;}
body{background-color:#FFFFFF;margin:20px;font-family:Verdana,Arial,Helvetica,sans-serif;font-size:12px;}
h1{color:#0099CC;}
.desc{background-color:#CFEBF7;border:1px #2580B2 solid;padding:15px 10px 15px 10px;margin:10px 0px 10px 0px;line-height:20px;min-width:900px;}
ul{font-size:11px;list-style:none;margin:10px 0px 10px 0px;padding:0px;width:100%;min-width:804px;}
li{list-style-type:none;width:100%;min-width:404px;height:20px;line-height:20px;display:inline-block;clear:both;}
li .T1-h{float:left;font-weight:bold;min-width:300px;}
li .T2-h{width:200px;float:right;font-weight:bold;}
li .T3-h{width:200px;float:right;font-weight:bold;}
li .T4-h{width:100px;float:right;font-weight:bold;}
li .T1{float:left;min-width:300px;}
li .T2{width:200px;float:right;}
li .T3{width:200px;float:right;}
li .T4{width:100px;float:right;}
</style>
</head>
<body>
	<h1>HTML Sitemap</h1>
	<div class="desc">
		This is an HTML Sitemap which is supposed to be processed by search engines like <a href="http://www.google.com">Google</a>, <a href="http://search.msn.com">MSN Search</a> and <a href="http://www.yahoo.com">Yahoo</a>.<br />
		With such a sitemap, it's much easier for the crawlers to see the complete structure of your site and retrieve it more efficiently.
	</div>
	<ul>
		<li>
			<div class="T1-h">URL</div>
			<div class="T2-h">Last Change</div>
			<div class="T3-h">Change Frequency</div>
			<div class="T4-h">Priority</div>
		</li>
EOT;
    public static $html_sitemap_element = '<li><div class="T1"><a href="%s" title="%s">%s</a></div><div class="T2">%s</div><div class="T3">%s</div><div class="T4">%s</div></li>';
    public static $html_sitemap_footer = '</ul></body></html>';
    
    public static $html_ext = '.html';
    public static $html_lastmod = '';
    public static $html_changefreq = 'monthly';
    public static $html_priority = '1.0';
   
    /**
     * 初始化目录
     *
     * @access public
     * @param array
     * @return
     */
    public static function init_path($type)
    {
        if(! in_array($type, self::$xml_type)){
            return FALSE;
        }
       
        $path = DISCUZ_ROOT.self::$xml_path.PLUGIN_NAME.'/';
        if(! is_dir($path)){
            mkdir($path);
            chmod($path, 0777);
        }
        
        $path .= $type.'/';
        if(! is_dir($path)){
            mkdir($path);
            chmod($path, 0777);
        }
        
        for($i=0; $i<10; $i++){
            $path_section = $path.$i.'/';
            if(! is_dir($path_section)){
                mkdir($path_section);
                chmod($path_section, 0777);
            }
        }
        
        return FALSE;
    }
    
    /**
     * 分卷id
     *
     * @access public
     * @param int
     * @return int
     */
    public static function rangeid_get($page)
    {
        $page = (string)$page;
        
        return intval($page{strlen($page) - 1});
    }
    
    /**
     * 帖子数据生成xml
     *
     * @access public
     * @param array
     * @return bool
     */
    public static function sitemap_thread_create($page, $list)
    {
        global $_G;
        
        if(! $list){
            return FALSE;
        }
        
        if(count($list) > self::$xml_url_limit){
            return FALSE;
        }
        
        $id = $page;
        $rangeid = self::rangeid_get($page);
        
        $filename = self::$xml_path.PLUGIN_NAME.'/thread/'.$rangeid.'/sitemap'.$page.self::$xml_ext;
        $filename_path = DISCUZ_ROOT.$filename;
  
        $thread_url = str_replace('&', '&amp;', self::get_url('thread'));
        $settings = lib_base::settings();

        $changefreq = $settings['changefreq'] ? $settings['changefreq'] : 'daily';
        $forum_priority = $settings['priority'] ? unserialize($settings['priority']) : array();
        
        $xml = self::$xml_header;
        
        foreach($list as $value){
            $loc = str_replace('{tid}', $value['tid'], $thread_url);
            $lastmod = date('Y-m-d', $value['lastpost']);
            $priority = in_array($value['fid'], $forum_priority) ? '1.0' : '0.5';
            $xml .= sprintf(self::$xml_element, $loc, $lastmod, $changefreq, $priority);
        }
        
        $xml .= self::$xml_footer;

        if(! file_exists($filename_path)){
            if(! file_put_contents($filename_path, $xml)){
                return FALSE;
            }
            chmod($filename_path, 0777);
        }else{
            if(! file_put_contents($filename_path, $xml)){
                return FALSE;
            }
        }
        
        //记录文件
        $add = array(
            'id'=>$id,
            'type'=>'thread',
            'filename'=>$filename,
            'counts'=>count($list),
            'updatetime'=>time()
        );

        lib_base::table(self::$table)->insert($add, FALSE, TRUE);
    
        //写入sitemap索引文件
        if(filter_var($_G['siteurl'], FILTER_VALIDATE_IP)) {
            return FALSE;
        }
        
        $loc = $_G['siteurl'].$filename;
        self::sitemap_write($loc);
        
        if($settings['is_open_html']){
            self::html_sitemap_thread_create($page, $list);
        }

        return TRUE;
    }
    
    /**
     * 文章数据生成xml
     *
     * @access public
     * @param array
     * @return bool
     */
    public static function sitemap_portal_create($page, $list)
    {
        global $_G;
    
        if(! $list){
            return FALSE;
        }
        
        if(count($list) > self::$xml_url_limit){
            return FALSE;
        }
    
        $id = $page;
        $rangeid = self::rangeid_get($page);
    
        $filename = self::$xml_path.PLUGIN_NAME.'/portal/'.$rangeid.'/sitemap'.$page.self::$xml_ext;
        $filename_path = DISCUZ_ROOT.$filename;
    
        $article_url = str_replace('&', '&amp;', lib_func_sitemap::get_url('article'));
        $settings = lib_base::settings();
    
        $changefreq = $settings['changefreq'] ? $settings['changefreq'] : 'daily';
        $priority = '1.0';
    
        $xml = self::$xml_header;
    
        foreach($list as $value){
            $loc = str_replace('{id}', $value['aid'], $article_url);
            $lastmod = date('Y-m-d', $value['dateline']);
            $xml .= sprintf(self::$xml_element, $loc, $lastmod, $changefreq, $priority);
        }
    
        $xml .= self::$xml_footer;
        
        if(! file_exists($filename_path)){
            if(! file_put_contents($filename_path, $xml)){
                return FALSE;
            }
            chmod($filename_path, 0777);
        }else{
            if(! file_put_contents($filename_path, $xml)){
                return FALSE;
            }
        }
        
        //记录文件
        $add = array(
            'id'=>$id,
            'type'=>'portal',
            'filename'=>$filename,
            'counts'=>count($list),
            'updatetime'=>time()
        );
        lib_base::table(self::$table)->insert($add, FALSE, TRUE);
        
        //写入sitemap索引文件
        if(filter_var($_G['siteurl'], FILTER_VALIDATE_IP)) {
            return FALSE;
        }
        
        $loc = $_G['siteurl'].$filename;
        self::sitemap_write($loc);
    
        if($settings['is_open_html']){
            self::html_sitemap_portal_create($page, $list);
        }
        
        return TRUE;
    }
    
    /**
     * 写入 sitemap
     *
     * @access public
     * @param string
     * @return bool
     */
    public static function sitemap_write($loc)
    {
        $filename = DISCUZ_ROOT.self::$xml_path.PLUGIN_NAME.'/'.self::$sitemap_filename;
        if(! file_exists($filename)){
            file_put_contents($filename, self::$sitemap_header.self::$sitemap_footer);
            chmod($filename, 0777);
        }
        
        $xml = file_get_contents($filename);

        if(strpos($xml, $loc))
        {
            $rule = '/<sitemap>(.*?)<\/sitemap>/';
            preg_match_all($rule, $xml, $result);
            
            if($result[0]){
                foreach($result[0] as $value){
                    if(strpos($value, $loc)){
                        $xml_element = sprintf(self::$sitemap_element, $loc, date('Y-m-d', time()));
                        $xml = str_replace($value, $xml_element, $xml);
                    }
                }
            }
        }
        else
        {
            $xml_element = sprintf(self::$sitemap_element, $loc, date('Y-m-d', time()));
            $xml = str_replace(self::$sitemap_footer, $xml_element.self::$sitemap_footer, $xml);
        }

        return file_put_contents($filename, $xml);
    }
    
    /**
     * 帖子数据生成html
     *
     * @access public
     * @param array
     * @return bool
     */
    public static function html_sitemap_thread_create($page, $list)
    {
        global $_G;
    
        if(! $list){
            return FALSE;
        }
    
        if(count($list) > self::$xml_url_limit){
            return FALSE;
        }
    
        $id = $page;
        $rangeid = self::rangeid_get($page);
    
        $filename = self::$xml_path.PLUGIN_NAME.'/thread/'.$rangeid.'/sitemap'.$page.self::$html_ext;
        $filename_path = DISCUZ_ROOT.$filename;
    
        $thread_url = self::get_url('thread');
        $settings = lib_base::settings();
    
        $changefreq = $settings['changefreq'] ? $settings['changefreq'] : 'daily';
        $forum_priority = $settings['priority'] ? unserialize($settings['priority']) : array();
    
        $xml = str_replace('%s', CHARSET, self::$html_sitemap_header);
    
        foreach($list as $value){
            $loc = str_replace('{tid}', $value['tid'], $thread_url);
            $lastmod = gmdate(DATE_ATOM, $value['lastpost']);
            $priority = in_array($value['fid'], $forum_priority) ? '1.0' : '0.5';
            $xml .= sprintf(self::$html_sitemap_element, $loc, $loc, $value['subject'], $lastmod, $changefreq, $priority);
        }
    
        $xml .= self::$html_sitemap_footer;
    
        if(! file_exists($filename_path)){
            if(! file_put_contents($filename_path, $xml)){
                return FALSE;
            }
            chmod($filename_path, 0777);
        }else{
            if(! file_put_contents($filename_path, $xml)){
                return FALSE;
            }
        }
    
        //写入sitemap索引文件
        if(filter_var($_G['siteurl'], FILTER_VALIDATE_IP)) {
            return FALSE;
        }
    
        $loc = $_G['siteurl'].$filename;
        self::html_sitemap_write('thread', $loc);
    
        return TRUE;
    }
    
    /**
     * 文章数据生成html
     *
     * @access public
     * @param array
     * @return bool
     */
    public static function html_sitemap_portal_create($page, $list)
    {
        global $_G;
    
        if(! $list){
            return FALSE;
        }
    
        if(count($list) > self::$xml_url_limit){
            return FALSE;
        }
    
        $id = $page;
        $rangeid = self::rangeid_get($page);
    
        $filename = self::$xml_path.PLUGIN_NAME.'/portal/'.$rangeid.'/sitemap'.$page.self::$html_ext;
        $filename_path = DISCUZ_ROOT.$filename;
    
        $article_url = lib_func_sitemap::get_url('article');
        $settings = lib_base::settings();
    
        $changefreq = $settings['changefreq'] ? $settings['changefreq'] : 'daily';
        $priority = '1.0';
    
        $xml = str_replace('%s', CHARSET, self::$html_sitemap_header);
    
        foreach($list as $value){
            $loc = str_replace('{id}', $value['aid'], $article_url);
            $lastmod = gmdate(DATE_ATOM, $value['dateline']);
            $xml .= sprintf(self::$html_sitemap_element, $loc, $loc, $value['title'], $lastmod, $changefreq, $priority);
        }
    
        $xml .= self::$html_sitemap_footer;
    
        if(! file_exists($filename_path)){
            if(! file_put_contents($filename_path, $xml)){
                return FALSE;
            }
            chmod($filename_path, 0777);
        }else{
            if(! file_put_contents($filename_path, $xml)){
                return FALSE;
            }
        }
    
        //写入sitemap索引文件
        if(filter_var($_G['siteurl'], FILTER_VALIDATE_IP)) {
            return FALSE;
        }
    
        $loc = $_G['siteurl'].$filename;
        self::html_sitemap_write('portal', $loc);
    
        return TRUE;
    }
    
    /**
     * 写入 sitemap
     *
     * @access public
     * @param string
     * @return bool
     */
    public static function html_sitemap_write($type, $loc)
    {
        $filename = DISCUZ_ROOT.self::$xml_path.PLUGIN_NAME.'/'.self::$html_sitemap_filename;
        if(! file_exists($filename)){
            file_put_contents($filename, str_replace('%s', CHARSET, self::$html_sitemap_header).self::$html_sitemap_footer);
            chmod($filename, 0777);
        }
    
        $xml = file_get_contents($filename);
    
        if(strpos($xml, $loc))
        {
            $rule = '/<li>(.*?)<\/li>/';
            preg_match_all($rule, $xml, $result);
    
            if($result[0]){
                foreach($result[0] as $value){
                    if(strpos($value, $loc)){
                        $name = basename($loc);
                        $xml_element = sprintf(self::$html_sitemap_element, $loc, $loc, $type.'-'.$name, gmdate(DATE_ATOM), 'always', 1);
                        $xml = str_replace($value, $xml_element, $xml);
                        break;
                    }
                }
            }
        }
        else
        {
            $name = basename($loc);
            $xml_element = sprintf(self::$html_sitemap_element, $loc, $loc, $type.'-'.$name, gmdate(DATE_ATOM), 'always', 1);
            $xml = str_replace(self::$html_sitemap_footer, $xml_element.self::$html_sitemap_footer, $xml);
        }
    
        return file_put_contents($filename, $xml);
    }
    
    /**
     * 主动推送
     *
     * @access public
     * @param array
     * @return array
     */
    public static function push_urls($urls, $type = 1)
    {
        global $_G;
        
        if(! $urls){
            return lib_base::back_array(lib_base::lang('push_url_nodata'));
        }
        
        $settings = $_G['cache']['plugin']['ror_sitemap'];

        $push_url = trim($settings['push_url']);
        if(! $push_url){
            return lib_base::back_array(lib_base::lang('push_url_nosetting'));
        }
        
        if($type == 2){
            $push_url = str_replace('urls', 'del', $push_url);
        }else if($type == 3){
            $push_url = str_replace('urls', 'update', $push_url);
        }
        
        if(! function_exists('curl_init')){
            return lib_base::back_array('Please install curl extension.');
        }

        $api = $push_url;
        $ch = curl_init();
        $options =  array(
            CURLOPT_URL => $api,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => implode("\n", $urls),
            CURLOPT_HTTPHEADER => array('Content-Type: text/plain'),
        );
        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);
        $error = curl_errno($ch);
        curl_close($ch);
        
        if($error != 0){
            return lib_base::back_array(sprintf(lib_base::lang('push_curl_error'), $error));
        }
        
        $result = json_decode($result, TRUE);

        $html = '<p>'.lib_base::lang('push_back_success').$result['success'].'</p>';
        $html .= '<p>'.lib_base::lang('push_back_remain').$result['remain'].'</p>';
//         $result['not_same_site'] && $html .= '<p>'.lib_base::lang('push_back_not_same_site').implode(',', $result['not_same_site']).'</p>';
//         $result['not_valid'] && $html .= '<p>'.lib_base::lang('push_back_not_valid').implode(',', $result['not_valid']).'</p>';
        
        return lib_base::back_array($html, 0);
    }
    
    /**
     * 静态链接
     *
     * @access public
     * @param string
     * @return string
     */
    public static function get_rewrite($item)
    {
        global $_G;
        
        $rewritestatus = $_G['setting']['rewritestatus'];
        $rewriterule = $_G['setting']['rewriterule'];

        if(in_array($item, $rewritestatus)){
            return $rewriterule[$item];
        }else{
            return false;
        }
    }
    
    /**
     * 帖子文章链接
     *
     * @access public
     * @param string
     * @return string
     */
    public static function get_url($type)
    {
        global $_G;
    
        $host = $_G['siteurl'];
        $url = '';
        if($type == 'thread'){
            $url = $host.'forum.php?mod=viewthread&tid={tid}';
            if(lib_base::settings('is_open_rewrite')){
                $rewrite = self::get_rewrite('forum_viewthread');
                if($rewrite){
                    $url = $host.str_replace(array('{page}','{prevpage}'), 1, $rewrite);
                }
            }
        }else if($type == 'article'){
            $url = $host.'portal.php?mod=view&aid={id}';
            if(lib_base::settings('is_open_rewrite')){
                $rewrite = self::get_rewrite('portal_article');
                if($rewrite){
                    $url = $host.str_replace('{page}', 1, $rewrite);
                }
            }
        }
        
        return $url;
    }
    
    /**
     * 删除目录
     *
     * @access public
     * @param string
     * @return string
     */
    public static function removeDir($dirName)
    {
        if(! is_dir($dirName)){
            return FALSE;
        }
    
        $handle = @opendir($dirName);
        while(($file = @readdir($handle)) !== FALSE)
        {
            if($file != '.' && $file != '..'){
                $dir = $dirName.'/'.$file;
                if(is_dir($dir)){
                    self::removeDir($dir);
                }else{
                    @unlink($dir);
                }
            }
        }
    
        closedir($handle);
    
        rmdir($dirName);
    }
}


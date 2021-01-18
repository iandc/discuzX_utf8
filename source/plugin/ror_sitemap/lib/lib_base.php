<?php
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

/**
 * lib_base Class
 * @package plugin
 * @subpackage ror
 * @category grab
 * @author ror
 * @link
 */
class lib_base 
{
    public static $grab_host = 'http://share555.com/';
    public static $grab_api_auth = 'api/grab/auth';
    
    public static function lang($langKey)
    {
        return lang('plugin/'.PLUGIN_NAME, $langKey);
    }

    public static function lang_message($langKey, $onlyString = true)
    {
        return $onlyString ? PLUGIN_NAME.':'. $langKey : self::lang($langKey);
    }

    public static function table($tableName)
    {
        return C::t('#'.PLUGIN_NAME.'#' .$tableName);
    }

    public static function template($templateName)
    {
        return template(PLUGIN_NAME.':'.$templateName);
    }

    public static function settings($settingKey = null)
    {
        static $settings = array();
        
        if (! $settings) {
            global $_G;

            loadcache('plugin');
            $settings = $_G['cache']['plugin'][PLUGIN_NAME];
        }

        if ($settingKey !== null) {
            return isset($settings[$settingKey]) ? $settings[$settingKey] : null;
        }

        return $settings;
    }
    
    /**
     * text message,$num = 0,1 1error 0right
     *
     * @access public
     * @param string, int
     * @return json
     */
    public static function back_text($text, $num = 1)
    {
        $data['state'] = $num;
        $data['result'] = $text;
        
        if(CHARSET == 'gbk'){
            $data = self::url_encode($data);
            echo urldecode(json_encode($data));
        }else{
            echo json_encode($data);
        }
        
        exit;
    }
    
    /**
     * json message,$num = 0,1 1error 0right
     *
     * @access public
     * @param array, int
     * @return json
     */
    public static function back_json($data, $num = 0)
    {
        $data['state']  = $num;
        
        if(CHARSET == 'gbk'){
            $data = self::url_encode($data);
            echo urldecode(json_encode($data));
        }else{
            echo json_encode($data);
        }
        
        exit;
    }
    
    public static function url_encode($arr)
    {
        foreach($arr as $k=>$v){
            if(is_array($v)){
                $arr[$k] = self::url_encode($v);
            }else{
                $arr[$k] = urlencode($v);
            }
        }
    
        return $arr;
    }
    
    /**
     * array message
     *
     * @access public
     * @param string, int
     * @return json
     */
    public static function back_array($text, $num = 1)
    {
        $data['state'] = $num;
        if(is_array($text)){
            $data = array_merge($data, $text);
        }else{
            $data['result'] = $text;
        }
    
        return $data;
    }
    
    /**
     * string message
     *
     * @access public
     * @param string
     * @return string
     */
    public static function back_echo($text)
    {
        header('Content-type: text/html; charset=utf-8');
        echo $text;
        exit;
    }
    
    /**
     * 输出html
     *
     * @access private
     * @param string, int
     * @return
     */
    public static function back_html($content, $type = 0)
    {
        $color = array(0=>'#009688', 1=>'#FF5722', 2=>'#FFB800');
    
        $type = isset($color[$type])?$type:0;
    
        echo '<p style="color:'.$color[$type].';">'.$content.'</p>';
    }
    
    /**
     * js back href
     *
     * @access public
     * @param string, string, int
     * @return
     */
    public static function back_url($message, $url, $delay = 1)
    {
        self::js_back_template($message.'<script type="text/javascript">setTimeout("window.location.href=\"'.$url.'\";", '.($delay*1000).');</script>');
    }
    
    /**
     * js back parent window
     *
     * @access public
     * @param string, int, string
     * @return
     */
    public static function js_back_window($message, $delay = 2)
    {
        self::js_back_template($message.'<script type="text/javascript">setTimeout("window.parent.location.reload();", '.($delay*1000).');</script>');
    }
    
    /**
     * js back parent href
     *
     * @access public
     * @param string, string, int, string
     * @return
     */
    public static function js_back_url($message, $url, $delay = 2)
    {
        self::js_back_template($message.'<script type="text/javascript">setTimeout("window.parent.location.href=\"'.$url.'\";", '.($delay*1000).');</script>');
    }
    
    /**
     * js back page
     *
     * @access public
     * @param string, int, string
     * @return
     */
    public static function js_back_page($message, $delay = 2)
    {
        self::js_back_template($message.'<script type="text/javascript">setTimeout("history.back();", '.($delay*1000).');</script>');
    }
    
    /**
     * js back close
     *
     * @access public
     * @param string, int, string
     * @return
     */
    public static function js_back_close($message, $delay = 2)
    {
        self::js_back_template($message.'<script type="text/javascript">setTimeout("window.parent.easyDialog.close();", '.($delay*1000).');</script>');
    }
    
    /**
     * show message
     *
     * @access public
     * @param string, int, string
     * @return
     */
    public static function js_back_show($message = '')
    {
        self::js_back_template($message);
    }
    
    /**
     * message template
     *
     * @access public
     * @param int, string
     * @return
     */
    public static function js_back_template($message)
    {
        include lib_base::template('notice');
        
        exit;
    }
    
    /**
     * url
     *
     * @access public
     * @param string
     * @return string
     */
    public static function url($act)
    {
        return 'plugin.php?id='.PLUGIN_NAME.'&act='.$act;
    }
    
    /**
     * admin url
     *
     * @access public
     * @param string
     * @return string
     */
    public static function admin_url($act)
    {
        return 'admin.php?action=plugins&operation=config&identifier='.PLUGIN_NAME.'&pmod=admin&act='.$act.'&myformhash='.FORMHASH;
    }
    
    /**
     * pic url
     *
     * @access public
     * @param string
     * @return string
     */
    public static function grab_pic_url()
    {
        return 'plugin.php?id='.PLUGIN_NAME.'&act=pic&url=';
    }
    
    /**
     * header url
     *
     * @access public
     * @param string
     * @return string
     */
    public static function header($url)
    {
        header('location:'.$url);
        exit;
    }
    
    /**
     * 转义处理
     *
     * @access public
     * @param string
     * @return string
     */
    public static function escape($string)
    {
        if(! $string){
            return '';
        }
        
        if(function_exists('mysql_connect')){
            $string = mysql_real_escape_string($string);
        }else{
            $string = addslashes($string);
        }
    
        return $string;
    }
    
    /**
     * 编码转换
     *
     * @access public
     * @param string
     * @return string
     */
    public static function convert_utf8_to_gbk($arr)
    {
        foreach($arr as $k => $v){
            if(is_array($v)){
                $arr[$k] = self::convert_utf8_to_gbk($v);
            }else{
                $arr[$k] = iconv('UTF-8', 'GBK//IGNORE', $v);
            }
        }

        return $arr;
    }
    
    /**
     * 编码转换
     *
     * @access public
     * @param string
     * @return string
     */
    public static function convert_gbk_to_utf8($arr)
    {
        foreach($arr as $k => $v){
            if(is_array($v)){
                $arr[$k] = self::convert_gbk_to_utf8($v);
            }else{
                $arr[$k] = iconv('GBK', 'UTF-8//IGNORE', $v);
            }
        }
    
        return $arr;
    }
    
    /**
     * 编码转换
     *
     * @access public
     * @param string
     * @return string
     */
    public static function string_utf8_to_gbk($string)
    {
        $string = iconv('UTF-8', 'GBK//IGNORE', $string);
        
        return $string;
    }
    
    /**
     * 编码转换
     *
     * @access public
     * @param string
     * @return string
     */
    public static function string_gbk_to_utf8($string)
    {
        $string = iconv('GBK', 'UTF-8//IGNORE', $string);
        
        return $string;
    }
}
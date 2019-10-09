<?php
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
class xcblog_utils
{
    public static function getvalues($variables, $keys, $subkeys = array()) 
	{
        $return = array();
        foreach($variables as $key => $value) {
            foreach($keys as $k) {
                if($k{0} == '/' && preg_match($k, $key) || $key == $k) {
                    if($subkeys) {
                        $return[$key] = mobile_core::getvalues($value, $subkeys);
                    } else {
                        if(!empty($value) || !empty($_GET['debug']) || (is_numeric($value) && intval($value) === 0 )) {
                            $return[$key] = is_array($value) ? mobile_core::arraystring($value) : (string)$value;
                        }   
                    }   
                }   
            }   
        }   
        return $return;
    }
	public static function array_sort_by(array &$arr,$key,$dir)
	{
		$sort = array();
        foreach ($arr as $k => &$v) {
            $sort[$k] = $v[$key];
        }
		$sortdir = $dir=='ASC' ? SORT_ASC : SORT_DESC;
        array_multisort($sort,$sortdir,$arr);
	}
    public static function toutf8($str)
    {   
        $charset = strtolower(CHARSET);
        return ($charset=='utf-8') ? $str : diconv($str,CHARSET,'utf-8');
    }   
    public static function tocharset($str)
    {   
        $charset = strtolower(CHARSET);
        return ($charset=='utf-8') ? $str : diconv($str,'utf-8',$charset);
    }
    public static function alltoutf8(array &$config)
    {
        foreach ($config as $key => &$value) {
            if (is_array($value)) {
                self::alltoutf8($value);
            } else {
                $value = self::toutf8($value);
            }
        }
    }
    public static function loadtpl($tpl, $vars ,$tplVars=null)
    {
        $json = json_encode($vars);
        $js_script = '<script type="text/javascript"> v = eval(\'(' . $json . ")');</script>\n";
        $content = @file_get_contents($tpl);
        if (false === $content) {
            return false;
        }
		if (is_string($content)) {
            $content = self::tocharset($content);
        }
		$tplVars['js_script'] = $js_script;
		$tplVars['app_charset'] = CHARSET;
		if (is_array($tplVars)) {
		    foreach($tplVars as $key => $value){
                $content = str_replace("<%".$key."%>",$value,$content);
                $content = str_replace("<% ".$key." %>",$value,$content);
            }
        }
		echo $content;
    }
}
?>
<?php
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
class xcblog_validate
{
	public static function getNCParameter($key, $name, $valueType='string', $maxlen=1024)
	{
		if (!isset($_REQUEST[$key]) && !isset($_FILES[$key])) {
			$msg = $key." is not set.";
			throw new Exception($msg);
			return null;
		}
        if (isset($_REQUEST[$key])) {
		    $value = trim($_REQUEST[$key]);
		    $_REQUEST[$key] = $value;
        }
		$res = true;
		switch($valueType) {
			case "string"  : $res = self::checkString($value, $maxlen); break;
			case "number"  : $res = self::checkNumber($value); break;
			case "integer" : $res = self::checkInteger($value); break;
			case "url"     : $res = self::checkUrl($key, $maxlen); break;
			case "email"   : $res = self::checkEmail($key); break;
			default:
				if (preg_match($valueType, $value)) {
					$res = true;
				} else {
					$res = "format error";
				}
				break;
		}
		if ($res !== true) {
			$msg = $name.$res;
			throw new Exception($msg);
		}
        return $_REQUEST[$key];
	}
	public static function getOPParameter($key, $name, $valueType='string', $maxlen=1024, $dafaultValue="")
	{
		if (!isset($_REQUEST[$key]) || $_REQUEST[$key]==="") {
			$_REQUEST[$key] = $dafaultValue;
			return $dafaultValue;
		}
		return self::getNCParameter($key, $name, $valueType, $maxlen);
	}
	private static function checkString($str_utf8, $maxlen)
	{
		if (mb_strlen($str_utf8, "UTF-8") > $maxlen) {
			return "is too long";
		}
		$illegalCharacters = array('delete', 'null', '||');
		foreach ($illegalCharacters as &$wd) {
			if (stristr($str_utf8, $wd)) {
				return "can not cantain char: $wd";
			}
		}
		return true;
	}
	private static function checkNumber($value)
	{
		if (!is_numeric($value)) {
			return "is not number";
		}
		return true;
	}
	private static function checkInteger($value)
	{
		$regex = "/^-?\d+$/";
		if (!preg_match($regex, $value)) {
			return "is not integer";			
		}
		return true;
	}
	private static function checkUrl($key, $maxlen)
	{
		$value = trim($_REQUEST[$key]);
		$res = self::checkString($value, $maxlen);
		if ($res !== true) {
			return $res;
		}
		$regex = "/^(https?:\/\/)?(([0-9a-z_!~*'().&=+$%-]+: )?[0-9a-z_!~*'().&=+$%-]+@)?(([0-9]{1,3}\.){3}[0-9]{1,3}|([0-9a-z_!~*'()-]+\.)*([0-9a-z][0-9a-z-]{0,61})?[0-9a-z]\.[a-z]{2,6})(:[0-9]{1,4})?((\/?)|(\/[^\s]+)+\/?)$/i";
		if (!preg_match($regex, $value)) {
			return "is not URL";
		}
		$initial = substr($value, 0, 4);
        if (strcmp($initial, "http") != 0) {
            $_REQUEST[$key] = "http://" . $value;
		}
		return true;
	}
	private static function checkEmail($value, $maxlen)
	{
		$regex = "/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/";
		if (!preg_match($regex, $value)) {
			return "is not Email";
		}
		return true;
	}
}
?>
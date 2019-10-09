<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

loadcache('plugin');
global $_G, $lang;

class SMSApi
{
    /*
     * $phone string 18812345678
     * $code  string 123456
     * $type  int 0ceshi 1zhuce 2shenfenyanzheng 3denglu 4xiugaimima 5yanzhengjiushouji
     * $uid   int
     * 
     * return
     * 1 success
     * 0 sms send error
     * -999 api weiqiyong
     * -998 peizhi error
     * -1 phone error
     * -2 code error
     * -3 file error
     * -4 duanxinxiane error
     * -5 duanxinliukong error
     * -6 checkip error
     */
    public function smssend($phone,$code,$type=0,$uid=0){
        $phone = daddslashes(trim($phone));
        $code = daddslashes(trim($code));
        $type = intval($type);
        $uid = intval($uid);
        
        if(file_exists(DISCUZ_ROOT.'./source/plugin/jzsjiale_sms/smstools.inc.php')){
            @include_once DISCUZ_ROOT.'./source/plugin/jzsjiale_sms/smstools.inc.php';
        }else{
            return -3;
        }
        
        if(empty($phone) || !preg_match("/^1[123456789]{1}\d{9}$/", $phone)){
            return -1;
        }
        
        if(empty($code)){
            return -2;
        }
        
        global $_G;
        $_config_smsapi = $_G['cache']['plugin']['jzsjiale_smsapi'];
        if(!$_config_smsapi['g_isopen']){
            return -999;
        }
        
        $_config = $_G['cache']['plugin']['jzsjiale_sms'];
        $g_accesskeyid = $_config['g_accesskeyid'];
        $g_accesskeysecret = $_config['g_accesskeysecret'];
        $webbianma = $_G['charset'];
        //$g_xiane = $_config['g_xiane'];
        $g_xiane = !empty($_config['g_xiane'])?$_config['g_xiane']:10;
        $g_zongxiane = ($_config['g_zongxiane']>0)?$_config['g_zongxiane']:0;
        $g_zhanghaoxiane = ($_config['g_zhanghaoxiane']>0)?$_config['g_zhanghaoxiane']:0;
        $g_isopenhtmlspecialchars = !empty($_config['g_isopenhtmlspecialchars'])?true:false;
        
        $g_templateid = "";
        $g_sign = "";


        $clentip = "";

        if($_config_smsapi['g_checkip']){
            if($_config['g_checkip'] == 1 || $_config['g_checkip'] == 2){

                if(file_exists(DISCUZ_ROOT.'./source/plugin/jzsjiale_sms/checkip.class.php')){
                    @include_once DISCUZ_ROOT.'./source/plugin/jzsjiale_sms/checkip.class.php';
                }else{
                    return -3;
                }
                $checkip = new Checkip();

                $clentip = $checkip->get_client_ip();

                $isipok = $checkip->ipaccessok();

                if(!$isipok){
                    return -6;
                }
            }
            if($_config['g_checkip'] == 5){

                if(file_exists(DISCUZ_ROOT.'./source/plugin/jzsjiale_sms/checkip.class.php')){
                    @include_once DISCUZ_ROOT.'./source/plugin/jzsjiale_sms/checkip.class.php';
                }else{
                    return -3;
                }
                $checkip = new Checkip();

                $clentip = $checkip->get_client_ip();

            }
            if($_config['g_checkip'] == 6 || $_config['g_checkip'] == 7){

                if(file_exists(DISCUZ_ROOT.'./source/plugin/jzsjiale_sms/checkipapi.class.php')){
                    @include_once DISCUZ_ROOT.'./source/plugin/jzsjiale_sms/checkipapi.class.php';
                }else{
                    return -3;
                }
                $checkip = new Checkipapi();

                $clentip = $checkip->get_client_ip();

                $isipok = $checkip->ipaccessok();

                if(!$isipok){
                    return -6;
                }
            }
        }

        
        if($g_zongxiane){
            $phonesendallcount = C::t('#jzsjiale_sms#jzsjiale_sms_smslist')->count_all_by_day();
        
            if($phonesendallcount >= $g_zongxiane){
                return -4;
            }
        }
        
        
        if($uid && $g_zhanghaoxiane && ($type == 2 || $type == 5)){
            $uidphonesendcount = C::t('#jzsjiale_sms#jzsjiale_sms_smslist')->count_by_uid_day($uid);
             
            if($uidphonesendcount >= $g_zhanghaoxiane){
                return -4;
            }
        }
        
        if(empty($g_accesskeyid)){
            return -998;
        }
        if(empty($g_accesskeysecret)){
            return -998;
        }
        
        if($type == 1){
            $g_openregister = $_config['g_openregister'];
        
            if(!$g_openregister){
                return -998;
            }else{
                $g_templateid = $_config['g_registerid'];
                $g_sign = $_config['g_registersign'];
            }
        }elseif($type == 2){
            $g_openyanzheng = $_config['g_openyanzheng'];
        
            if(!$g_openyanzheng){
                return -998;
            }else{
                $g_templateid = $_config['g_yanzhengid'];
                $g_sign = $_config['g_yanzhengsign'];
            }
        }elseif($type == 3){
            $g_openlogin = $_config['g_openlogin'];
        
            if(!$g_openlogin){
                return -998;
            }else{
                $g_templateid = $_config['g_loginid'];
                $g_sign = $_config['g_loginsign'];
            }
        }elseif($type == 4){
            $g_openmima = $_config['g_openmima'];
        
            if(!$g_openmima){
                return -998;
            }else{
                $g_templateid = $_config['g_mimaid'];
                $g_sign = $_config['g_mimasign'];
            }
        }elseif($type == 5){
            $g_openyanzheng = $_config['g_openyanzheng'];
        
            if(!$g_openyanzheng){
                return -998;
            }else{
                $g_templateid = $_config['g_yanzhengid'];
                $g_sign = $_config['g_yanzhengsign'];
            }
        }else{
            $g_openyanzheng = $_config['g_openyanzheng'];
        
            if(!$g_openyanzheng){
                return -998;
            }else{
                $g_templateid = $_config['g_yanzhengid'];
                $g_sign = $_config['g_yanzhengsign'];
            }
        }

        if($_config_smsapi['g_checkip']){
            if($_config['g_checkip'] == 3 || $_config['g_checkip'] == 4){

                if(file_exists(DISCUZ_ROOT.'./source/plugin/jzsjiale_sms/checkphone.class.php')){
                    @include_once DISCUZ_ROOT.'./source/plugin/jzsjiale_sms/checkphone.class.php';
                }else{
                    return -3;
                }
                $checkphone = new Checkphone();

                $checkdata = $checkphone->phoneaccessok($phone);
                $clentip = $checkdata['province'].'-'.$checkdata['city'].'-'.$checkdata['operator'];

                $isipok = $checkdata['code'];

                if(!$isipok){
                    return -6;
                }

            }

        }

        $phonesendcount = C::t('#jzsjiale_sms#jzsjiale_sms_smslist')->count_by_phone_day($phone);
        
        if($phonesendcount >= $g_xiane){
            return -4;
        }
        
        if(empty($g_templateid)){
            return -998;
        }
        if(empty($g_sign)){
            return -998;
        }
   
       
        $sms_param_array = array();
        $sms_param_array['code']=(string)$code;
        
        
        if(($type == 0 && $_config['g_openyanzhengproduct']) || ($type == 1 && $_config['g_openregisterproduct']) || ($type == 2 && $_config['g_openyanzhengproduct']) || ($type == 3 && $_config['g_openloginproduct']) || ($type == 4 && $_config['g_openmimaproduct']) || ($type == 5 && $_config['g_openyanzhengproduct'])){
            $g_product = $_config['g_product'];
            $sms_param_array['product']=!empty($g_product)?$g_product:'';
            $sms_param_array['product'] =$this->getbianma($sms_param_array['product'],$webbianma,$g_isopenhtmlspecialchars);
        }
        
        $sms_param = json_encode($sms_param_array);
        
        $g_sign=$this->getbianma($g_sign,$webbianma,$g_isopenhtmlspecialchars);
        
        //quoqishijian
        $g_youxiaoqi = $_config['g_youxiaoqi'];
        if(empty($g_youxiaoqi)){
            $g_youxiaoqi = 600;
        }
        //echo "====".date('Y-m-d H:i:s',strtotime("+".$g_youxiaoqi." second"));exit;
        $expire = strtotime("+".$g_youxiaoqi." second");
        
        
        $retdata = "";
        $phonecode = C::t('#jzsjiale_sms#jzsjiale_sms_code')->fetchfirst_by_phone($phone);
        if ($phonecode) {
            if (($phonecode['dateline'] + 60) > TIMESTAMP) {
                return -5;
            } else {
                $smstools = new SMSTools();
                $smstools->__construct($g_accesskeyid, $g_accesskeysecret);
                $retdata = $smstools->smssend($code,$expire,$type,$uid,$phone,$g_sign,$g_templateid,$sms_param,$clentip);
            }
        } else {
            $smstools = new SMSTools();
            $smstools->__construct($g_accesskeyid, $g_accesskeysecret);
            $retdata = $smstools->smssend($code,$expire,$type,$uid,$phone,$g_sign,$g_templateid,$sms_param,$clentip);
        }
        
        switch ($retdata){
            case 'success':
                return 1;
            case 'isv.MOBILE_NUMBER_ILLEGAL':
                return -1;
            case 'isv.BUSINESS_LIMIT_CONTROL':
                return -5;
            case 'error':
                return 0;
            default:
                return 0;
        }
        
    }
    
    public function getbianma($data, $webbianma = "gbk",$openhtmlspecialchars = true)
    {
        if ($webbianma == "gbk") {
            $data = diconv($data, 'GB2312', 'UTF-8');
        }
        if($openhtmlspecialchars){
            $data = isset($data) ? trim(htmlspecialchars($data, ENT_QUOTES)) : '';
        }
        return $data;
    }
}
?>
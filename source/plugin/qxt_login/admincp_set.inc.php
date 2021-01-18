<?php

if (!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
    exit('Access Denied');
}
include_once DISCUZ_ROOT . './source/plugin/qxt_login/sms.func.php';
$actions = addslashes($_GET['actions']);
if ($actions == null) {
    if (!submitcheck('configsubmit')) {
        $set_sms_title = lang('plugin/qxt_login', 'set_sms');
        if (isset($_G['setting']['qxt_login_setting'])) {
            $smsset = unserialize($_G['setting']['qxt_login_setting']);
            $smsurl = "https://sms.100sms.cn";
            $queryaction = "uid=$smsset[smsuid]&username=$smsset[smsname]&token=$smsset[token]&appid=$smsset[appid]&apitype=1";
            $smsnum = smsportnum($smsurl, $queryaction, "post");
            if (is_numeric($smsnum)) {
                if (intval($smsnum) === 0) {
                    $set_sms_title .= lang('plugin/qxt_login', 'set_sms_num0');
                } else {
                    $set_sms_title .= lang('plugin/qxt_login', 'set_sms_num', array('smsnum' => $smsnum, 'url' => ADMINSCRIPT . "?action=plugins&operation=config&do=$pluginid&identifier=qxt_login&pmod=admincp_set&actions=test"));
                }
            } else {
                $set_sms_title .= lang('plugin/qxt_login', 'set_sms_no', array('err' => $smsnum));
            }
        }
        //showtips(lang('plugin/qxt_login', 'set_tips'));
        showformheader("plugins&operation=config&do=$plugin[pluginid]&identifier=qxt_login&pmod=admincp_set");
        showtableheader($set_sms_title);
        //showsetting(lang('plugin/qxt_login', 'smsname'), 'smsname', stripslashes($smsset['smsname']), 'text', 0, 0, lang('plugin/qxt_login', 'smsname_comment'));
        //showsetting(lang('plugin/qxt_login', 'smsuid'), 'smsuid', stripslashes($smsset['smsuid']), 'text', 0, 0, lang('plugin/qxt_login', 'smsuid_comment'));
        //showsetting(lang('plugin/qxt_login', 'token'), 'token', stripslashes($smsset['token']), 'text', 0, 0, lang('plugin/qxt_login', 'token_comment'));
        //showsetting(lang('plugin/qxt_login', 'appid'), 'appid', stripslashes($smsset['appid']), 'text', 0, 0, lang('plugin/qxt_login', 'appid_comment'));
        showsetting(lang('plugin/qxt_login', 'url'), 'url', stripslashes($smsset['url']), 'text', 0, 0, lang('plugin/qxt_login', 'url_comment'));
        showsetting(lang('plugin/qxt_login', 'key'), 'key', stripslashes($smsset['key']), 'text', 0, 0, lang('plugin/qxt_login', 'key_comment'));
        showsubmit('configsubmit', 'submit');
        showtablefooter();
        showformfooter();
    } else {
        if (empty($_GET['smsname']) || empty($_GET['smsuid']) || empty($_GET['token']) || empty($_GET['appid'])) {
            //cpmsg(lang('plugin/qxt_login', 'set_error'), '', 'error');
        }
        if (empty($_GET['url']) || empty($_GET['key'])) {
            cpmsg(lang('plugin/qxt_login', 'set_error'), '', 'error');
        }
        $data = array(
            //'smsname' => addslashes(trim($_GET['smsname'])),
            //'smsuid' => intval($_GET['smsuid']),
            //'token' => addslashes(trim($_GET['token'])),
            //'appid' => intval($_GET['appid']),

            'url' => addslashes(trim($_GET['url'])),
            'key' => addslashes(trim($_GET['key'])),
        );
        C::t('common_setting')->update_batch(array("qxt_login_setting" => $data));
        updatecache('setting');
        cpmsg(lang('plugin/qxt_login', 'suc_3'), "action=plugins&operation=config&do=$pluginid&identifier=qxt_login&pmod=admincp_set", 'succeed');
    }
} elseif ($actions == 'test') {
    if (!submitcheck('sendsubmit')) {
        showformheader("plugins&operation=config&do=$plugin[pluginid]&identifier=qxt_login&pmod=admincp_set&actions=test");
        showtableheader(lang('plugin/qxt_login', 'set_sms_test'));
        showsetting(lang('plugin/qxt_login', 'testmb'), 'testmb', '', 'text', 0, 0);
        showsetting(lang('plugin/qxt_login', 'testmsg'), 'testmsg', lang('plugin/qxt_login', 'testmsg_content'), 'textarea', 0, 0);
        showsubmit('sendsubmit', 'submit');
        showtablefooter();
        showformfooter();
    } else {
        $testmb = addslashes(trim($_GET['testmb']));
        $testmsg = addslashes(trim($_GET['testmsg']));
        $smsportlog = "";
        if(!preg_match("/^1[12345789]{1}\d{9}$/",$testmb)){
            cpmsg(lang('plugin/qxt_login', 'err_1'), '', 'error');
        }
        if(!$testmsg){
            cpmsg(lang('plugin/qxt_login', 'err_0'), '', 'error');
        }
        $smsstatus = sendsms($testmb, $testmsg);
        $smsportlog = diconv($smsportlog, "gbk", CHARSET);
        $data = array(
            'mobile' => $testmb,
            'msg' => $testmsg,
            'status' => $smsstatus,
            'smslog' => $smsportlog,
            'dateline' => TIMESTAMP
        );
        DB::insert('qxt_login_smslist', $data);
        if($smsstatus==1){
            cpmsg(lang('plugin/qxt_login', 'sms_suc'), "action=plugins&operation=config&do=$pluginid&identifier=qxt_login&pmod=admincp_set&actions=test", 'succeed');
        }else{
            cpmsg(lang('plugin/qxt_login', 'sms_err',array('err' => $smsportlog)), '', 'error');
        }
    }
}
?>
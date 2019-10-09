<?php

if (!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
    exit('Access Denied');
}
include_once DISCUZ_ROOT . './source/plugin/qxt_login/sms.func.php';

if (!submitcheck('sendsubmit') && !$_GET['sendall']) {
    if (submitcheck('smssubmit')) {
        $mobile = implode("\r\n", $_GET['mobiles']);
    }
    showtips(lang('plugin/qxt_login', 'sendsms_tips'));
    showformheader("plugins&operation=config&do=$plugin[pluginid]&identifier=qxt_login&pmod=admincp_smssend");
    showtableheader(lang('plugin/qxt_login', 'sendsms'));
    showtagheader('tbody', 'mbtab', true);
    showsetting(lang('plugin/qxt_login', 'mobile'), 'mobile', $mobile, 'textarea', 0, 0);
    showtagfooter('tbody');
    showsetting(lang('plugin/qxt_login', 'smsmsg'), 'smsmsg', '', 'textarea', 0, 0);
    showtagheader('tbody', 'pagetab');
    showsetting('members_newsletter_num', 'pertask', 100, 'text');
    showtagfooter('tbody');
    showsubmit('sendsubmit', lang('plugin/qxt_login', 'sendsms'), '', '<input name="sendall" class="checkbox" type="checkbox" value="1" onclick="$(\'mbtab\').style.display = this.value == 1 ? \'none\' : \'\'; $(\'pagetab\').style.display = this.value == 1 ? \'\' : \'none\'; this.value = this.value == 1 ? 2 : 1;" id="sendall" /><label for="sendall">' . lang('plugin/qxt_login', 'sendall') . '</label>');
    showtablefooter();
    showformfooter();
} else {
    if ($_GET['sendall']) {
        $smsmsg = daddslashes(trim($_GET['smsmsg']));
        if (!$smsmsg) {
            cpmsg(lang('plugin/qxt_login', 'err_0'), '', 'error');
        }
        $page = max(1, intval($_GET['page']));
        $tpp = !empty($_GET['pertask']) ? $_GET['pertask'] : '100';
        if ($tpp > 1000) {
            cpmsg(lang('plugin/qxt_login', 'err_11'), '', 'error');
        }
        $start = ($page - 1) * $tpp;
        $count = DB::result(DB::query("SELECT COUNT(*) FROM " . DB::table('qxt_login_user')), 0);
        if ($start >= $count) {
            cpmsg(lang('plugin/qxt_login', 'sms_suc'), "action=plugins&operation=config&do=$pluginid&identifier=qxt_login&pmod=admincp_smssend", 'succeed');
        }
        $qxtusers = DB::fetch_all("SELECT * FROM " . DB::table('qxt_login_user') . " ORDER BY uid DESC LIMIT $start,$tpp");
        $mobiles = array();
        foreach ($qxtusers as $qxtuser) {
            $mobiles[]= $qxtuser[mobile];
        }
        $mobile = implode(",", $mobiles);
        $smsportlog = "";
        $smsstatus = sendsms($mobile, $smsmsg);
        $smsportlog = diconv($smsportlog, "gbk", CHARSET);
        $data = array(
            'mobile' => $mobile,
            'msg' => $smsmsg,
            'status' => $smsstatus,
            'smslog' => $smsportlog,
            'dateline' => TIMESTAMP
        );
        DB::insert('qxt_login_smslist', $data);
        $num = $page*$tpp > $count ? $count : $page*$tpp;
        $page++;
        cpmsg(lang('plugin/qxt_login', 'smsloading'), "action=plugins&operation=config&do=$pluginid&identifier=qxt_login&pmod=admincp_smssend&sendall=1&pertask=$tpp&smsmsg=$smsmsg&page=$page", "loadingform", array('count' => $count, 'num' => $num));
    } else {
        $mbs = daddslashes(trim($_GET['mobile']));
        $smsmsg = daddslashes(trim($_GET['smsmsg']));
        if (!$smsmsg || !$mbs) {
            cpmsg(lang('plugin/qxt_login', 'err_0'), '', 'error');
        }
        //$mbs_arr = explode("\r\n", $mbs);
        preg_match_all("|1[34578]\d{9}|U", $mbs, $mobiles);
        $mobiles = array_unique($mobiles[0]);
        if (count($mobiles) > 1000) {
            cpmsg(lang('plugin/qxt_login', 'err_11'), '', 'error');
        }
        $mobile = implode(",", $mobiles);

        $smsportlog = "";
        $smsstatus = sendsms($mobile, $smsmsg);
        $smsportlog = diconv($smsportlog, "gbk", CHARSET);
        $data = array(
            'mobile' => $mobile,
            'msg' => $smsmsg,
            'status' => $smsstatus,
            'smslog' => $smsportlog,
            'dateline' => TIMESTAMP
        );
        DB::insert('qxt_login_smslist', $data);
        if ($smsstatus == 1) {
            cpmsg(lang('plugin/qxt_login', 'sms_suc'), "action=plugins&operation=config&do=$pluginid&identifier=qxt_login&pmod=admincp_smssend", 'succeed');
        } else {
            cpmsg(lang('plugin/qxt_login', 'sms_err', array('err' => $smsportlog)), '', 'error');
        }
    }
}
?>
<?php

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
if (submitcheck('bindsubmit')) {

    $mobile = daddslashes($_GET['mobile']);
    $smsseccode = daddslashes($_GET['smsseccode']);
    if (!$smsseccode || !$mobile)
        showmessage(lang('plugin/qxt_login', 'err_0'));
    if (!preg_match("/^1[12345789]{1}\d{9}$/", $mobile)) {
        showmessage(lang('plugin/qxt_login', 'err_1'));
    }

    loaducenter();
    list($result) = uc_user_login($_G['uid'], $_GET['passwd'], 1, 0);
    if ($result < 0)
        showmessage(lang('plugin/qxt_login', 'err_8'));

    $qxtsec = DB::fetch_first("SELECT * FROM " . DB::table('qxt_login_seccode') . " WHERE mobile = '$mobile' AND seccode = '$smsseccode'");
    if ($qxtsec) {
        if ((TIMESTAMP - $qxtsec[dateline]) > $_G['cache']['plugin']['qxt_login']['secexpiry']) {
            DB::delete('qxt_login_seccode', "seccode = '$smsseccode'");
            showmessage(lang('plugin/qxt_login', 'err_5'));
        }
    } else {
        showmessage(lang('plugin/qxt_login', 'err_6'));
    }

    $data = array(
        'uid' => $_G['uid'],
        'username' => $_G['username'],
        'mobile' => $mobile,
        'dateline' => TIMESTAMP
    );
    if ($_G['bindmb']) {
        DB::update('qxt_login_user', $data, "uid = $_G[uid]");
    } else {
        DB::insert('qxt_login_user', $data);
    }
    DB::delete('qxt_login_seccode', "seccode = '$smsseccode'");
    showmessage(lang('plugin/qxt_login', 'suc_1'), dreferer(), array(), array('alert' => 'right', 'locationtime' => true, 'msgtype' => 2, 'showdialog' => true, 'showmsg' => true));
}
?>

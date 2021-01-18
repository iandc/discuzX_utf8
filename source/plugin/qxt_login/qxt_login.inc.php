<?php

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
include_once DISCUZ_ROOT . './source/plugin/qxt_login/sms.func.php';
$plugininfo = $_G['cache']['plugin']['qxt_login'];
$action = addslashes($_GET['action']);
if (empty($action)) {
    if (!$plugininfo['loginbysms']) {
        exit;
    }
    $navtitle = lang('plugin/qxt_login', 'qxt_login_navtitle');
    require_once libfile('function/misc');
    loaducenter();
    if ($_G['uid']) {
        $referer = dreferer();
        $ucsynlogin = $_G['setting']['allowsynlogin'] ? uc_user_synlogin($_G['uid']) : '';
        $param = array('username' => $_G['member']['username'], 'usergroup' => $_G['group']['grouptitle'], 'uid' => $_G['member']['uid']);
        showmessage('login_succeed', $referer ? $referer : './', $param, array('showdialog' => 1, 'locationtime' => true, 'extrajs' => $ucsynlogin));
    }
    require_once libfile('function/member');

    if (submitcheck('loginsubmit')) {
        $mobile = daddslashes($_GET['mobile']);
        $smsseccode = daddslashes($_GET['smsseccode']);
        if (!$smsseccode || !$mobile)
            showmessage(lang('plugin/qxt_login', 'err_0'));
        if (!preg_match("/^1[12345789]{1}\d{9}$/", $mobile)) {
            showmessage(lang('plugin/qxt_login', 'err_1'));
        }
        $qxtsec = DB::fetch_first("SELECT * FROM " . DB::table('qxt_login_seccode') . " WHERE mobile = '$mobile' AND seccode = '$smsseccode'");
        if ($qxtsec) {
            if ((TIMESTAMP - $qxtsec[dateline]) > $_G['cache']['plugin']['qxt_login']['secexpiry']) {
                DB::delete('qxt_login_seccode', "seccode = '$smsseccode'");
                showmessage(lang('plugin/qxt_login', 'err_5'));
            }
        } else {
            showmessage(lang('plugin/qxt_login', 'err_6'));
        }
        DB::delete('qxt_login_seccode', "seccode = '$smsseccode'");
        $_G['uid'] = $_G['member']['uid'] = 0;
        $_G['username'] = $_G['member']['username'] = $_G['member']['password'] = '';
        $uid = DB::result_first("SELECT uid FROM " . DB::table('qxt_login_user') . " WHERE mobile = '$mobile'");
        $member = getuserbyuid($uid, 1);
        if (!$member || empty($member['uid'])) {
            DB::delete('qxt_login_user', "mobile = '$mobile'");
            showmessage(lang('plugin/qxt_login', 'err_10'));
        }
        if ($member['_inarchive']) {
            C::t('common_member_archive')->move_to_master($member['uid']);
        }
        setloginstatus($member, $_GET['cookietime'] ? 2592000 : 0);
        checkfollowfeed();
        if ($_G['group']['forcelogin']) {
            if ($_G['group']['forcelogin'] == 1) {
                clearcookies();
                showmessage('location_login_force_qq');
            } elseif ($_G['group']['forcelogin'] == 2 && $_GET['loginfield'] != 'email') {
                clearcookies();
                showmessage('location_login_force_mail');
            }
        }
        if ($_G['member']['lastip'] && $_G['member']['lastvisit']) {
            dsetcookie('lip', $_G['member']['lastip'] . ',' . $_G['member']['lastvisit']);
        }
        C::t('common_member_status')->update($_G['uid'], array('lastip' => $_G['clientip'], 'port' => $_G['remoteport'], 'lastvisit' => TIMESTAMP, 'lastactivity' => TIMESTAMP));
        $ucsynlogin = $_G['setting']['allowsynlogin'] ? uc_user_synlogin($_G['uid']) : '';

        if ($_G['member']['adminid'] != 1) {
            if ($_G['setting']['accountguard']['loginoutofdate'] && $_G['member']['lastvisit'] && TIMESTAMP - $_G['member']['lastvisit'] > 90 * 86400) {
                C::t('common_member')->update($_G['uid'], array('freeze' => 2));
                C::t('common_member_validate')->insert(array(
                    'uid' => $_G['uid'],
                    'submitdate' => TIMESTAMP,
                    'moddate' => 0,
                    'admin' => '',
                    'submittimes' => 1,
                    'status' => 0,
                    'message' => '',
                    'remark' => '',
                        ), false, true);
                manage_addnotify('verifyuser');
                showmessage('location_login_outofdate', 'home.php?mod=spacecp&ac=profile&op=password&resend=1', array('type' => 1), array('showdialog' => true, 'striptags' => false, 'locationtime' => true));
            }
        }

        $param = array(
            'username' => $_G['member']['username'],
            'usergroup' => $_G['group']['grouptitle'],
            'uid' => $_G['member']['uid'],
            'groupid' => $_G['groupid'],
            'syn' => $ucsynlogin ? 1 : 0
        );
        $extra = array(
            'showdialog' => true,
            'locationtime' => true,
            'extrajs' => $ucsynlogin
        );
        $loginmessage = $_G['groupid'] == 8 ? 'login_succeed_inactive_member' : 'login_succeed';
        $location = $_G['groupid'] == 8 ? 'home.php?mod=space&do=home' : dreferer();
        showmessage($loginmessage, $location, $param, $extra);
    } else {
        include template('qxt_login:login');
    }
} elseif ($action == 'sendsms') {
    if ($_GET['formhash'] == formhash()) {
        if (!check_seccode($_GET['seccodeverify'], $_GET['seccodehash'])) {
            showmsg('f', lang('message', 'submit_seccode_invalid'));
        }
        if (!preg_match("/^1[12345789]{1}\d{9}$/", $_GET['mobile'])) {
            showmsg('f', lang('plugin/qxt_login', 'err_1'));
        }
        $thetype = intval($_GET[type]);
        $mb_bool = DB::fetch_first("SELECT * FROM " . DB::table('qxt_login_user') . " WHERE mobile = '$_GET[mobile]'");
        if ($mb_bool && ($thetype == 1 || $thetype == 2)) {
            showmsg('f', lang('plugin/qxt_login', 'err_2'));
        }
        if (!$mb_bool && ($thetype == 3 || $thetype == 4)) {
            showmsg('f', lang('plugin/qxt_login', 'err_9'));
        }
        $t_smsnum = DB::result_first("SELECT COUNT(*) FROM " . DB::table('qxt_login_smslist') . " WHERE mobile = '$_GET[mobile]' AND dateline > " . strtotime(date('Y-m-d')));
        if ($plugininfo[limitnum] && ($t_smsnum + 1) > $plugininfo[limitnum]) {
            showmsg('f', lang('plugin/qxt_login', 'err_7'));
        }
        do {
            $smssec = random(6, 1);
        } while (DB::fetch_first("SELECT * FROM " . DB::table('qxt_login_seccode') . " WHERE seccode = '$smssec' AND dateline>" . (TIMESTAMP - $plugininfo[secexpiry])));
        $thesec = DB::fetch_first("SELECT * FROM " . DB::table('qxt_login_seccode') . " WHERE mobile = '$_GET[mobile]'");
        if ($thesec) {
            if (($thesec[dateline] + 60) > TIMESTAMP) {
                showmsg('f', lang('plugin/qxt_login', 'err_3', array('secexpiry' => $plugininfo[secexpiry])));
            } else {
                $data = array(
                    'seccode' => $smssec,
                    'dateline' => TIMESTAMP
                );
                DB::update('qxt_login_seccode', $data, "mobile = '$_GET[mobile]'");
            }
        } else {
            $data = array(
                'mobile' => $_GET[mobile],
                'seccode' => $smssec,
                'dateline' => TIMESTAMP
            );
            DB::insert('qxt_login_seccode', $data);
        }

        if ($thetype == 1) {
            $smsmsg = $plugininfo[secregmsg];
        } elseif ($thetype == 2) {
            $smsmsg = $plugininfo[bindmsg];
        } elseif ($thetype == 3) {
            $smsmsg = $plugininfo[loginmsg];
        } elseif ($thetype == 4) {
            $smsmsg = $plugininfo[getpassmsg];
        }

        $smsmsg = str_replace("{sec}", $smssec, $smsmsg);
        $smsportlog = "";
        $smsstatus = sendsms($_GET[mobile], $smsmsg);
        $smsportlog = diconv($smsportlog, "gbk", CHARSET);
        $data = array(
            'mobile' => $_GET[mobile],
            'msg' => $smsmsg,
            'status' => $smsstatus,
            'smslog' => $smsportlog,
            'dateline' => TIMESTAMP
        );
        DB::insert('qxt_login_smslist', $data);
        if ($smsstatus != 1) {
            showmsg('f', lang('plugin/qxt_login', 'err_4'));
        }
        showmsg('s', '');
    } else {
        include template('qxt_login:sendsms');
    }
} elseif ($action == 'getpass') {
    if (!$plugininfo['getpassbysms']) {
        exit;
    }
    if (submitcheck('lostpwsubmit')) {
        $mobile = daddslashes($_GET['mobile']);
        $smsseccode = daddslashes($_GET['smsseccode']);
        if (!$smsseccode || !$mobile)
            showmessage(lang('plugin/qxt_login', 'err_0'));
        if (!preg_match("/^1[12345789]{1}\d{9}$/", $mobile)) {
            showmessage(lang('plugin/qxt_login', 'err_1'));
        }
        $qxtsec = DB::fetch_first("SELECT * FROM " . DB::table('qxt_login_seccode') . " WHERE mobile = '$mobile' AND seccode = '$smsseccode'");
        if ($qxtsec) {
            if ((TIMESTAMP - $qxtsec[dateline]) > $_G['cache']['plugin']['qxt_login']['secexpiry']) {
                DB::delete('qxt_login_seccode', "seccode = '$smsseccode'");
                showmessage(lang('plugin/qxt_login', 'err_5'));
            }
        } else {
            showmessage(lang('plugin/qxt_login', 'err_6'));
        }
        DB::delete('qxt_login_seccode', "seccode = '$smsseccode'");
        $uid = DB::result_first("SELECT uid FROM " . DB::table('qxt_login_user') . " WHERE mobile = '$mobile'");
        $member = getuserbyuid($uid, 1);
        if (!$member) {
            showmessage('getpasswd_account_notmatch');
        } elseif ($member['adminid'] == 1 || $member['adminid'] == 2) {
            showmessage('getpasswd_account_invalid');
        }
        $table_ext = $member['_inarchive'] ? '_archive' : '';
        $idstring = random(6);
        C::t('common_member_field_forum' . $table_ext)->update($member['uid'], array('authstr' => "$_G[timestamp]\t1\t$idstring"));
        require libfile('function/member');
        $sign = make_getpws_sign($member['uid'], $idstring);
        showmessage('getpasswd_send_succeed', "member.php?mod=getpasswd&uid=$uid&id=$idstring&sign=$sign", array(), array('showdialog' => 1, 'locationtime' => true));
    }
}

function showmsg($type, $msg) {
    $infos = array();
    if ($type == 's') {
        $infos['flag'] = 'success';
        $infos['message'] = diconv($msg, CHARSET, 'UTF-8');
        echo json_encode($infos);
        exit;
    } elseif ($type == 'f') {
        $infos['flag'] = 'fail';
        $infos['message'] = diconv($msg, CHARSET, 'UTF-8');
        echo json_encode($infos);
        exit;
    }
}

?>
<?php

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

class plugin_qxt_login {

    public function plugin_qxt_login() {
        global $_G;
        if ($_G['uid']) {
            $_G['bindmb'] = DB::result_first("SELECT mobile FROM " . DB::table('qxt_login_user') . " WHERE uid = $_G[uid]");
        }
    }

    function common() {
        global $_G;
        $smsseccode = daddslashes(getcookie('smsseccode'));
        if ($_G['uid'] && $smsseccode && !$_G['bindmb']) {
            $_G['bindmb'] = DB::result_first("SELECT mobile FROM " . DB::table('qxt_login_seccode') . " WHERE seccode = '$smsseccode'");
            $data = array(
                'uid' => $_G['uid'],
                'username' => $_G['username'],
                'mobile' => $_G['bindmb'],
                'dateline' => TIMESTAMP
            );
            DB::insert('qxt_login_user', $data);
            DB::delete('qxt_login_seccode', "seccode = '$smsseccode'");
            dsetcookie('smsseccode');
        }
    }
    
    function global_login_extra() {
        global $_G;
        if (!$_G['cache']['plugin']['qxt_login']['loginbysms']) {
            return;
        }
        include_once template('qxt_login:smslogin');
        return $smslogin;
    }

    public function global_usernav_extra1() {
        global $_G;
        if (!$_G['uid'])
            return;
        if (!$_G['bindmb']) {
            $url = "home.php?mod=spacecp&ac=plugin&id=qxt_login:bind";
            $res = "<span class='pipe'>|</span><a href='$url'><img src='./source/plugin/qxt_login/images/mb_bind.gif' align='absmiddle' style='border-radius:2px;'/></a>&nbsp;";
            return $res;
        }
        return;
    }

}

class plugin_qxt_login_member extends plugin_qxt_login {

    function register_input_output() {
        global $_G;
        if (!$_G['cache']['plugin']['qxt_login']['issecreg']) {
            return;
        }
        include_once template('qxt_login:reghook');
        return $reghook;
    }

    function register_code() {
        global $_G;
        if($_G['uid']){
            return;
        }
        if ($_G['cache']['plugin']['qxt_login']['issecreg']) {
            $_G[setting][seccodedata][rule][register][allow] = 0;
            if (submitcheck('regsubmit', 0, $seccodecheck, $secqaacheck)) {
                $themobile = addslashes($_GET['mobile_Qxt']);
                $thesmssec = addslashes($_GET['smssec']);
                if (!$themobile || !$thesmssec) {
                    showmessage(lang('plugin/qxt_login', 'err_0'));
                }
                $qxtsec = DB::fetch_first("SELECT * FROM " . DB::table('qxt_login_seccode') . " WHERE mobile = '$themobile' AND seccode = '$thesmssec'");
                if ($qxtsec) {
                    if ((TIMESTAMP - $qxtsec[dateline]) > $_G['cache']['plugin']['qxt_login']['secexpiry']) {
                        DB::delete('qxt_login_seccode', "mobile = '$themobile'");
                        showmessage(lang('plugin/qxt_login', 'err_5'));
                    }
                } else {
                    showmessage(lang('plugin/qxt_login', 'err_6'));
                }
                dsetcookie('smsseccode', $thesmssec, 600);
            }
        }
    }

    function logging_code() {
        global $_G;
        if($_G['uid']){
            return;
        }
        if ($_G['cache']['plugin']['qxt_login']['loginbymb']) {
            $mb = daddslashes(trim($_GET['username']));
            if (preg_match("/^1[34578]{1}\d{9}$/", $mb)) {
                if (submitcheck('loginsubmit')) {
                    $bind = DB::fetch_first("SELECT * FROM " . DB::table('qxt_login_user') . " WHERE mobile = '$mb'");
                    if ($bind['uid']) {
                        $_GET['username'] = $bind['uid'];
                        $_GET['fastloginfield'] = $_GET['loginfield'] = 'uid';
                        $_G['setting']['autoidselect'] = false;
                        $_G['setting']['uidlogin'] = 1;
                    }
                }
            }
        }
        if(!$_G['uid'] && isset($_GET['viewlostpw']) && $_G['cache']['plugin']['qxt_login']['getpassbysms'] && !$_GET['byemail']){
            include template('qxt_login:lostpwhook');
            exit;
        }
    }
}
?>
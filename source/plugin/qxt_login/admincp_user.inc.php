<?php

if (!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
    exit('Access Denied');
}
$actions = !empty($_GET['actions']) ? $_GET['actions'] : '';
if ($actions == null) {
    if ($_GET['uid'] && $_GET['model'] == 'del' && $_GET['formhash'] == formhash()) {
        DB::delete('qxt_login_user', "uid = " . intval($_GET['uid']));
    }

    $page = max(1, intval($_GET['page']));
    $select[$_GET['tpp']] = $_GET['tpp'] ? "selected='selected'" : '';
    $tpp_options = "<option value='20' $select[20]>20</option><option value='50' $select[50]>50</option><option value='100' $select[100]>100</option>";
    $tpp = !empty($_GET['tpp']) ? $_GET['tpp'] : '20';
    $start = ($page - 1) * $tpp;

    showtips(lang('plugin/qxt_login', 'qxtuser_tips'));
    showformheader("plugins&operation=config&do=$plugin[pluginid]&identifier=qxt_login&pmod=admincp_user");
    showtableheader();
    showtablerow('', array('width="50"', 'width="60"', 'width="40"', ''), array(
        cplang('perpage'),
        "<select name=\"tpp\">$tpp_options</select>",
        cplang('keywords'),
        "<input size=\"20\" name=\"keyword\" type=\"text\" value=\"$_GET[keyword]\" />
    <input class=\"btn\" type=\"submit\" value=\"" . cplang('search') . "\" />"
            )
    );
    showtablefooter();
    showformfooter();

    $keyword = addslashes($_GET['keyword']);
    if (!empty($keyword)) {
        $sql1 = " AND (uid = " . intval($keyword) . " OR mobile = '" . $keyword . "' OR username LIKE '%" . $keyword . "%')";
    }

    showformheader("plugins&operation=config&do=$plugin[pluginid]&identifier=qxt_login&pmod=admincp_smssend");
    showtableheader();
    showsubtitle(array('', 'UID', cplang('username'), lang('plugin/qxt_login', 'user_mb'), lang('plugin/qxt_login', 'user_dateline'), cplang('operation')));
    $count = DB::result(DB::query("SELECT COUNT(*) FROM " . DB::table('qxt_login_user') . " WHERE 1 $sql1"), 0);
    $qxtusers = DB::fetch_all("SELECT * FROM " . DB::table('qxt_login_user') . " WHERE 1 $sql1 ORDER BY uid DESC LIMIT $start,$tpp");
    foreach ($qxtusers as $qxtuser) {
        $theuid = $qxtuser[uid];
        echo showtablerow('', array('', '', '', '', ''), array(
            "<input class=\"checkbox\" type=\"checkbox\" name=\"mobiles[$theuid]\" value=\"$qxtuser[mobile]\" />",
            $theuid,
            "<a href=\"home.php?mod=space&uid=$theuid\" target=\"_blank\">" . stripslashes($qxtuser[username]) . "</a>",
            $qxtuser[mobile],
            date('Y-m-d H:i:s', $qxtuser[dateline]),
            "<a href=\"" . ADMINSCRIPT . "?action=plugins&operation=config&do=$pluginid&identifier=qxt_login&pmod=admincp_user&model=del&uid=$theuid&formhash=" . FORMHASH . "\" onclick=\"return confirm(&quot;" . lang('plugin/qxt_login', 'login_del_xw', array('username' => $qxtuser[username])) . "&quot;);\" class=\"act\">" . lang('plugin/qxt_login', 'user_del') . "</a>
            <a href=\"" . ADMINSCRIPT . "?action=plugins&operation=config&do=$pluginid&identifier=qxt_login&pmod=admincp_user&actions=edit&uid=$qxtuser[uid] \" class=\"act\" >$lang[edit]</a>
            <a href=\"" . ADMINSCRIPT . "?action=members&operation=edit&uid=$qxtuser[uid]\" class=\"act\">$lang[detail]</a>"
                ), TRUE);
    }
    $multi = multi($count, $tpp, $page, ADMINSCRIPT . '?action=plugins&operation=config&do=' . $pluginid . '&identifier=qxt_login&pmod=admincp_user', 1000);
    echo '<tr><td colspan="6"><div class="cuspages right"><div class="pg">' . $multi . '</div></div>'
    . '<div><a href="' . ADMINSCRIPT . '?action=plugins&operation=config&do=' . $pluginid . '&identifier=qxt_login&pmod=admincp_user&actions=add" class="addtr">' . lang('plugin/qxt_login', 'user_add') . '</a></div></td></tr>';

    showsubmit('', '', '', '<input type="checkbox" name="chkall" id="chkall" class="checkbox" onclick="checkAll(\'prefix\', this.form, \'mobiles\')" /><label for="chkall">' . cplang('select_all') . '</label>&nbsp;&nbsp;<input type="submit" class="btn" name="smssubmit" value="' . lang('plugin/qxt_login', 'sendsms') . '" />');

    showtablefooter();
    showformfooter();
} elseif ($actions == 'edit') {
    $id = intval($_GET['uid']);
    if (!$id) {
        cpmsg(lang('plugin/qxt_login', 'err_0'), '', 'error');
    }
    $thebind = DB::fetch_first("SELECT * FROM " . DB::table('qxt_login_user') . " WHERE uid=$id");
    if (!submitcheck('editsubmit')) {
        $editheader = lang('plugin/qxt_login', 'user_edit') . " - " . $thebind[username];
        showformheader("plugins&operation=config&do=$pluginid&identifier=qxt_login&pmod=admincp_user&actions=edit&uid=$id");
        showtableheader($editheader);
        showsetting(lang('plugin/qxt_login', 'user_mb'), 'mobile', stripslashes($thebind['mobile']), 'text', 0, 0);
        showsubmit('editsubmit');
        showtablefooter();
        showformfooter();
    } else {
        $mb = daddslashes($_GET['mobile']);
        if (!preg_match("/^1[34578]{1}\d{9}$/", $mb)) {
            cpmsg(lang('plugin/qxt_login', 'err_1'), '', 'error');
        }
        if (DB::fetch_first("SELECT * FROM " . DB::table('qxt_login_user') . " WHERE mobile = '$mb'")) {
            cpmsg(lang('plugin/qxt_login', 'err_2'), '', 'error');
        }
        $data = array(
            'mobile' => $mb,
            'dateline' => TIMESTAMP
        );
        DB::update('qxt_login_user', $data, "uid = $id");
        cpmsg(lang('plugin/qxt_login', 'suc_1'), "action=plugins&operation=config&do=$pluginid&identifier=qxt_login&pmod=admincp_user", "succeed");
    }
} elseif ($actions == 'add') {
    $id = intval($_GET['uid']);
    if (!submitcheck('addsubmit')) {
        $header = lang('plugin/qxt_login', 'user_add');
        showformheader("plugins&operation=config&do=$pluginid&identifier=qxt_login&pmod=admincp_user&actions=add");
        showtableheader($header);
        showsetting(cplang('username'), 'username', '', 'text', 0, 0);
        showsetting(lang('plugin/qxt_login', 'user_mb'), 'mobile', '', 'text', 0, 0);
        showsubmit('addsubmit');
        showtablefooter();
        showformfooter();
    } else {
        $theusername = daddslashes($_GET['username']);
        $mb = daddslashes($_GET['mobile']);
        if (!$theusername || !$mb) {
            cpmsg(lang('plugin/qxt_login', 'err_0'), '', 'error');
        }
        if (!preg_match("/^1[34578]{1}\d{9}$/", $mb)) {
            cpmsg(lang('plugin/qxt_login', 'err_1'), '', 'error');
        }
        if (DB::fetch_first("SELECT * FROM " . DB::table('qxt_login_user') . " WHERE mobile = '$mb'")) {
            cpmsg(lang('plugin/qxt_login', 'err_2'), '', 'error');
        }
        if (DB::fetch_first("SELECT * FROM " . DB::table('qxt_login_user') . " WHERE username = '$theusername'")) {
            cpmsg(lang('plugin/qxt_login', 'err_10'), '', 'error');
        }
        $theuid = C::t('common_member')->fetch_uid_by_username($theusername);
        if (!$theuid) {
            cpmsg('founder_perm_member_noexists', '', 'error', array('name' => $theusername));
        }
        $data = array(
            'uid' => $theuid,
            'username' => $theusername,
            'mobile' => $mb,
            'dateline' => TIMESTAMP
        );
        DB::insert('qxt_login_user', $data);
        cpmsg(lang('plugin/qxt_login', 'suc_1'), "action=plugins&operation=config&do=$pluginid&identifier=qxt_login&pmod=admincp_user", "succeed");
    }
}
?>
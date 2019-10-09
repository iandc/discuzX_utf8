<?php

if (!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
    exit('Access Denied');
}
$id = intval($_GET['id']);
$actions = !empty($_GET['actions']) ? $_GET['actions'] : '';
if ($actions == null) {
    if ($id && $_GET['model'] == 'del' && $_GET['formhash'] == formhash()) {
        DB::delete('qxt_login_smslist', "id = $id");
    }

    $page = max(1, intval($_GET['page']));
    $select[$_GET['tpp']] = $_GET['tpp'] ? "selected='selected'" : '';
    $tpp_options = "<option value='20' $select[20]>20</option><option value='50' $select[50]>50</option><option value='100' $select[100]>100</option>";
    $tpp = !empty($_GET['tpp']) ? $_GET['tpp'] : '20';
    $starttime = isset($_GET['starttime']) ? $_GET['starttime'] : date('Y-m-d');
    $start = ($page - 1) * $tpp;
    $status = isset($_GET['status']) ? $_GET['status'] : 'all';
    $select[$status] = $status ? "selected='selected'" : '';
    $status_options = "<option value=\"all\"  " . $select[all] . ">" . cplang('all') . "</option>"
            . "<option value='-1' " . $select[-1] . ">" . lang('plugin/qxt_login', 'smsstatus_-1') . "</option>"
            . "<option value='1' $select[1]>" . lang('plugin/qxt_login', 'smsstatus_1') . "</option>";
    $select[$status] = '';

    showtips(lang('plugin/qxt_login', 'smstips'));
    showformheader("plugins&operation=config&do=$plugin[pluginid]&identifier=qxt_login&pmod=admincp_smslist&page=$page");
    showtableheader();
    echo '<script src="static/js/calendar.js" type="text/javascript"></script>';
    showtablerow('', array('width="50"', 'width="60"', 'width="75"', 'width="80"', 'width="50"', 'width="60"', 'width="40"', ''), array(
        cplang('perpage'),
        "<select name=\"tpp\">$tpp_options</select>",
        lang('plugin/qxt_login', 'smsstarttime'),
        "<input type=\"text\" onclick=\"showcalendar(event, this)\" value=\"$starttime\" name=\"starttime\" class=\"txt\">",
        lang('plugin/qxt_login', 'smsstatus'),
        "<select name=\"status\">$status_options</select>",
        cplang('keywords'),
        "<input size=\"20\" name=\"keyword\" type=\"text\" value=\"$_GET[keyword]\" />
    <input class=\"btn\" type=\"submit\" value=\"" . cplang('search') . "\" />"
            )
    );
    showtablefooter();
    $sql1 = " AND dateline > " . strtotime($starttime);

    if ($status <> '' && $status != 'all') {
        if(intval($status)==1){
            $sql1.=" AND status = 1";
        }else{
            $sql1.=" AND status <> 1";
        }
    }
    $keyword = addslashes($_GET['keyword']);
    if (!empty($keyword)) {
        $sql1.= " AND (mobile LIKE '%" . $keyword . "%' OR msg LIKE '%" . $keyword . "%')";
    }
    $extra = "&starttime=$starttime&status=$status&tpp=$tpp&keyword=$keyword";
    showtableheader();
    showsubtitle(array('ID', lang('plugin/qxt_login', 'mobile'), lang('plugin/qxt_login', 'smsmsg'), lang('plugin/qxt_login', 'smsdateline'), lang('plugin/qxt_login', 'smsstatus'), lang('plugin/qxt_login', 'smsportlog'), cplang('operation')));
    $count = DB::result_first("SELECT COUNT(*) FROM " . DB::table('qxt_login_smslist') . " WHERE 1" . $sql1);
    $lists = DB::fetch_all("SELECT * FROM " . DB::table('qxt_login_smslist') . " WHERE 1 $sql1 ORDER BY id desc LIMIT $start,$tpp");
    foreach ($lists as $list) {
        $mobiles = explode(",", $list['mobile']);
        $mobile = $mobiles[0];
        if (count($mobiles) > 1) {
            $mobile .= "<br>" . $mobiles[1];
        }
        if (count($mobiles) > 2) {
            $mobile .= "<br>".lang('plugin/qxt_login', 'smsnum', array('num' => count($mobiles)));
        }
        echo showtablerow('', array('', '', '', '', '', '', 'width="90"'), array(
            $list[id],
            $mobile,
            stripslashes($list[msg]),
            date('Y-m-d H:i:s', $list['dateline']),
            $list[status] == 1 ? lang('plugin/qxt_login', 'smsstatus_1') : lang('plugin/qxt_login', 'smsstatus_-1'),
            $list[smslog],
            "<a href=\"" . ADMINSCRIPT . "?action=plugins&operation=config&do=$pluginid&identifier=qxt_login&pmod=admincp_smslist&model=del&id=$list[id]&$extra&formhash=" . FORMHASH . "\" onclick=\"return confirm(&quot;" . lang('plugin/qxt_login', 'smsdel_xw', array('listid' => $list[id])) . "&quot;);\" class=\"act\" >" . $lang[delete] . "</a>
            <a href=\"" . ADMINSCRIPT . "?action=plugins&operation=config&do=$pluginid&identifier=qxt_login&pmod=admincp_smslist&actions=export&id=$list[id] \" class=\"act\" >$lang[export]</a>"
                ), TRUE);
    }
    $multi = multi($count, $tpp, $page, ADMINSCRIPT . "?action=plugins&operation=config&do=$pluginid&identifier=qxt_login&pmod=admincp_smslist$extra", 1000);
    echo '<tr><td colspan="7"><div class="cuspages right"><div class="pg">' . $multi . '</div></div></td></tr>';
    showtablefooter();
} elseif ($actions == 'export') {
    $lid = intval($_GET['id']);
    if (!$lid) {
        cpmsg(lang('plugin/qxt_login', 'err_0'), '', 'error');
    }
    $mobile = DB::result_first("SELECT mobile FROM " . DB::table('qxt_login_smslist') . " WHERE id = $lid");
    $mobiles = explode(",", $mobile);
    foreach ($mobiles as $row) {
        $detail .= $row . "\r\n";
    }
    $filename = date('Ymd', TIMESTAMP) . '.txt';

    ob_end_clean();
    header('Content-Encoding: none');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename=' . $filename);
    header('Pragma: no-cache');
    header('Expires: 0');
    if ($_G['charset'] != 'gbk') {
        $detail = diconv($detail, $_G['charset'], 'GBK');
    }
    echo $detail;
    exit();
}
?>
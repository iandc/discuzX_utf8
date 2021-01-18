<?php

/**
 *   @CopyRight   http://www.docswf.com
 *   @author      QQ50245077
 */
if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
require './source/plugin/doc/function.php';
require_once libfile('function/credit');
session_start();
global $_G;
$doc = $_G['cache']['plugin']['doc'];
$doc_name = $doc['doc_name'];
$doc_upgroups = unserialize($doc['doc_upgroups']);
$doc_noverifygroups = unserialize($doc['doc_noverifygroups']);
$doc_readcredit = $doc['doc_readcredit'];
$doc_readcreditname = GetExtcreditesNameByID($doc_readcredit, $_G[setting][creditnames]);
$doc_readcreditsize = $doc['doc_readcreditsize'];
$doc_downcredit = $doc['doc_downcredit'];
$doc_downcreditname = GetExtcreditesNameByID($doc_downcredit, $_G[setting][creditnames]);
$doc_downcreditsize = $doc['doc_downcreditsize'];
$doc_uploadcredit = $doc['doc_uploadcredit'];
$doc_uploadcreditname = GetExtcreditesNameByID($doc_uploadcredit, $_G[setting][creditnames]);
$doc_uploadcreditsize = $doc['doc_uploadcreditsize'];
$doc_docsize = $doc['doc_docsize'];
$doc_rewrite = $doc['doc_rewrite'];

$doc_yun = $doc['doc_yun'];
$doc_copy = $doc['doc_copy'];
$doc_h5 = 0;//$doc['doc_h5'];
$doc_ypage = $doc['doc_ypage'];

if (!$doc_docsize) {
    $doc_docsize = ini_get('post_max_size');
}

if (!$_G['uid']) {
    showmessage(lang('plugin/doc', 'pleaselogin'), '', array(), array('login' => true));
    exit(0);
}
if (!in_array($_G[groupid], $doc_upgroups)) {
    showmessage(lang('plugin/doc', 'norightupload'));
    exit(0);
}

if (submitcheck('action') && $_GET['action'] == "submit") {
    $title = sql_inject($_GET['title']);
    $short = sql_inject($_GET['short']);
    $doctype = sql_inject($_GET['doctype']);
    $key = sql_inject($_GET['key']);
    $freepage = sql_inject($_GET['freepage']);
    $readpay = sql_inject($_GET['readpay']);
    $downpay = sql_inject($_GET['downpay']);
    $docpath = sql_inject($_GET['docpath']);
    $docsize = sql_inject($_GET['docsize']);

    $_G['charset'] == 'gbk' ? $title = iconv('utf-8', 'gbk//IGNORE', $title) : $title;
    $_G['charset'] == 'gbk' ? $short = iconv('utf-8', 'gbk//IGNORE', $short) : $short;
    $_G['charset'] == 'gbk' ? $key = iconv('utf-8', 'gbk//IGNORE', $key) : $key;

    $userid = $_G['uid'];
    $username = $_G['username'];
    $date = date('Y-m-d', time());
    $viewcount = 0;
    $downcount = 0;
    $isimport = 0;
    $state = in_array($_G[groupid], $doc_noverifygroups) ? 1 : 0;
    $data_array = array(
        'docpath' => $docpath,
        'docsize' => $docsize,
        'title' => $title,
        'short' => $short,
        'doctype' => $doctype,
        'key' => $key,
        'freepage' => $freepage,
        'readpay' => $readpay,
        'downpay' => $downpay,
        'userid' => $userid,
        'username' => $username,
        'uploaddate' => $date,
        'viewcount' => $viewcount,
        'downcount' => $downcount,
        'isimport' => $isimport,
        'state' => $state
    );
    DB::insert('ds_docinfo', $data_array);

    if ($state == 1) {
        _updatemembercount($userid, array($doc_uploadcredit => $doc_uploadcreditsize), true, '', -1, lang('plugin/doc', 'credit_upload'), lang('plugin/doc', 'credit_upload'), lang('plugin/doc', 'credit_upload2'));
    }

    $tip = lang('plugin/doc', 'upload_updateok');
    $_docdir = 'source/plugin/doc/';
    if ($doc_yun) {
        $from_url = substr($_G['siteurl'], 0, strripos($_G['siteurl'], $_G['siteroot']));
        if (ISloacal($from_url)) {
            $tip = lang('plugin/doc', 'upload_localhost');
        } else {
            $from_dir = $_G['siteroot'] . $_docdir;
            $rtn = Cpost($from_url, $from_dir, $docpath, $from_url . $from_dir . $docpath, $title . '.' . GetExten($docpath), $doc_ypage, $doc_copy, $doc_h5, $_G['charset'] == 'gbk');
            switch ($rtn) {
                case '1':
                case '2':
                    if (!file_exists($_docdir . $docpath))
                        $tip = lang('plugin/doc', 'upload_updateno');
                    break;
                case '-1':
                    $tip = lang('plugin/doc', 'upload_novip');
                    break;
                case '-2':
                    $tip = lang('plugin/doc', 'upload_vip');
                    break;
                default :
                    $tip = lang('plugin/doc', 'upload_noread');
            }
        }
        echo $tip;
        exit(0);
    } else {
        if (file_exists($_docdir . $docpath)) {
            $file_jm_name = str_replace("/", "@", $docpath);
			fopen($_docdir . "data/temp/$doc_copy@$doc_h5@$doc_ypage@" . $file_jm_name, "w");
        } else {
            $tip = lang('plugin/doc', 'upload_updateno');
        }
        echo $tip;
        exit(0);
    }
}

$typeop0 = '<select><option value="-1">' . lang('plugin/doc', 'pleaseSelect') . '</option>';
$dis = 'style="display:none"';
foreach (GetDocType(0) as $i => $t) {
    $typeop0 .= "<option value=\"$t[0]\" $sel>$t[1]</option>";
    $sec = GetDocType($t[0]);
    if ($sec) {
        $typeop1 .= "<select name=\"seltype_$t[0]\" $dis><option value=\"-1\">" . lang('plugin/doc', 'pleaseSelect') . '</option>';
        foreach ($sec as $ii => $tt) {
            $typeop1 .= "<option value=\"$tt[0]\" $sel>$tt[1]</option>";
            $thr = GetDocType($tt[0]);
            if ($thr) {
                $typeop2 .= "<select name=\"seltype_$tt[0]\" $dis><option value=\"-1\">" . lang('plugin/doc', 'pleaseSelect') . '</option>';
                foreach ($thr as $iii => $ttt) {
                    $typeop2 .= "<option value=\"$ttt[0]\" $sel>$ttt[1]</option>";
                    $four = GetDocType($ttt[0]);
                    if ($four) {
                        $typeop3 .= "<select name=\"seltype_$ttt[0]\" $dis><option value=\"-1\">" . lang('plugin/doc', 'pleaseSelect') . '</option>';
                        foreach ($four as $iiii => $tttt) {
                            $typeop3 .= "<option value=\"$tttt[0]\" $sel>$tttt[1]</option>";
                        }
                        $typeop3 .= '</select>';
                    }
                }
                $typeop2 .= '</select>';
            }
        }
        $typeop1 .= '</select>';
    }
}
$typeop0 .= '</select>';

$navtitle = $doc_name;
$metakeywords = $doc_name;
$metadescription = $doc_name;

include template('doc:upload');

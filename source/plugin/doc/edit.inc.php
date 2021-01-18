<?php

/**
 *   @CopyRight   http://www.docswf.com
 *   @author      QQ50245077
 */
if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
require './source/plugin/doc/function.php';
session_start();
global $_G;
$doc = $_G['cache']['plugin']['doc'];
$doc_name = $doc['doc_name'];
$doc_upgroups = unserialize($doc['doc_upgroups']);
$doc_mangegroups = unserialize($doc['doc_mangegroups']);
$doc_readcredit = $doc['doc_readcredit'];
$doc_readcreditname = GetExtcreditesNameByID($doc_readcredit, $_G[setting][creditnames]);
$doc_downcredit = $doc['doc_downcredit'];
$doc_downcreditname = GetExtcreditesNameByID($doc_downcredit, $_G[setting][creditnames]);
$doc_rewrite = $doc['doc_rewrite'];

$doc_oss = $doc['doc_oss'];
$doc_oss_delfile = $doc['doc_oss_delfile'];
$doc_oss_fluxoss = $doc['doc_oss_fluxoss'];
if ($doc_oss) {
    require './source/plugin/doc/Oss/Oss.php';
}

$docid = $_GET['d'] == null ? 0 : $_GET['d']; 
if (!$_G['uid']) {
    showmessage(lang('plugin/doc', 'pleaselogin'), '', array(), array('login' => true));
    exit(0);
}
if (!in_array($_G[groupid], $doc_upgroups)) {
    showmessage(lang('plugin/doc', 'norighteidt'));
    exit(0);
}

if (submitcheck('action') && $_GET['action'] == "submit") {
    $title = $_GET['title'];        
    $short = $_GET['short'];        
    $doctype = $_GET['doctype'];    
    $key = $_GET['key'];            
    $freepage = $_GET['freepage'];  
    $readpay = $_GET['readpay'];    
    $downpay = $_GET['downpay'];    

    $_G['charset'] == 'gbk' ? $title = iconv('utf-8', 'gbk', $title) : $title;
    $_G['charset'] == 'gbk' ? $short = iconv('utf-8', 'gbk', $short) : $short;
    $_G['charset'] == 'gbk' ? $key = iconv('utf-8', 'gbk', $key) : $key;

    UpdateDocInfo($docid, $title, $short, $key, $freepage, $readpay, $downpay, $doctype);
    echo lang('plugin/doc', 'edit_editok');
    exit(0);
}
$typeop0 = '<select><option value="-1">' . lang('plugin/doc', 'pleaseSelect') . '</option>';
$dis = 'style="display:none"';
foreach (GetDocType(0) as $i => $t) {
    $typeop0.="<option value=\"$t[0]\" $sel>$t[1]</option>";
    $sec = GetDocType($t[0]);
    if ($sec) {
        $typeop1.="<select name=\"seltype_$t[0]\" $dis><option value=\"-1\">" . lang('plugin/doc', 'pleaseSelect') . '</option>';
        foreach ($sec as $ii => $tt) {
            $typeop1.="<option value=\"$tt[0]\" $sel>$tt[1]</option>";
            $thr = GetDocType($tt[0]);
            if ($thr) {
                $typeop2.="<select name=\"seltype_$tt[0]\" $dis><option value=\"-1\">" . lang('plugin/doc', 'pleaseSelect') . '</option>';
                foreach ($thr as $iii => $ttt) {
                    $typeop2.="<option value=\"$ttt[0]\" $sel>$ttt[1]</option>";
					$four = GetDocType($ttt[0]);
                    if ($four) {
                        $typeop3 .= "<select name=\"seltype_$ttt[0]\" $dis><option value=\"-1\">" . lang('plugin/doc', 'pleaseSelect') . '</option>';
                        foreach ($four as $iiii => $tttt) {
                            $typeop3 .= "<option value=\"$tttt[0]\" $sel>$tttt[1]</option>";
                        }
                        $typeop3 .= '</select>';
                    }
                }
                $typeop2.='</select>';
            }
        }
        $typeop1.='</select>';
    }
}
$typeop0.='</select>';

$DocInfo = GetDocInfo($docid);
$docexeten = $DocInfo[1];
$title = $DocInfo[5];
$short = $DocInfo[6];
$doctype = $DocInfo[7];
$key = $DocInfo[8];
$freepage = (int) $DocInfo[9];
$readpay = $DocInfo[10];
$downpay = $DocInfo[11];
$userid = $DocInfo[12];

if ($_G['uid'] != $userid && !in_array($_G[groupid], $doc_mangegroups)) {
    showmessage(lang('plugin/doc', 'edit_edityourdoc'), '', array(), array('login' => true));
    exit(0);
}

$navtitle = $title . ',' . $doc_name;
$metakeywords = $doc_name;
$metadescription = $doc_name;

include template('doc:edit');

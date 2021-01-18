<?php

/**
 *   @CopyRight   http://www.docswf.com
 *   @author      QQ50245077
 */
if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
require './source/plugin/doc/function.php';
global $_G;
$doc = $_G['cache']['plugin']['doc'];
$doc_name = $doc['doc_name'];
//$doc_ad3 = $doc['doc_ad3'];
$doc_readcredit = $doc['doc_readcredit'];
$doc_readcreditname = GetExtcreditesNameByID($doc_readcredit, $_G[setting][creditnames]);
$doc_downcredit = $doc['doc_downcredit'];
$doc_downcreditname = GetExtcreditesNameByID($doc_downcredit, $_G[setting][creditnames]);
$doc_mangegroups = unserialize($doc['doc_mangegroups']);
$doc_rewrite = $doc['doc_rewrite'];
if (!$_G['uid']) {
    showmessage(lang('plugin/doc', 'pleaselogin'), '', array(), array('login' => true));
    exit(0);
}

$type = $_GET['t'] == null ? "0-0-0-0" : $_GET['t'];
$type = explode("-", $type);
$fir = $type[0];
$sec = $type[1];
$thr = $type[2];
$four = $type[3];
$dtype = 0;
$navtype = "";
if ($fir != 0) {
    $dtype = $fir;
    $navtype.=GetDocTypeName($fir) . ' ';
}
if ($sec != 0) {
    $dtype = $sec;
    $navtype.=GetDocTypeName($sec) . ' ';
}
if ($thr != 0) {
    $dtype = $thr;
    $navtype.=GetDocTypeName($thr) . ' ';
}
if ($four != 0) {
    $dtype = $four;
    $navtype.=GetDocTypeName($four) . ' ';
}
$keyword = $_GET['k'] == null ? "" : $_GET['k'];
if ($doc_rewrite) {
    $_G['charset'] == 'gbk' ? $keyword = iconv('utf-8', 'gbk//IGNORE', $keyword) : $keyword;
}

$state = $_GET['s'] == null ? "1" : $_GET['s'];
$uid = $_G['uid'];
$cpage = $_GET['p'] == null ? 1 : $_GET['p'];
$pagesize = 20;
if (in_array($_G[groupid], $doc_mangegroups)) {
    $uid = "";
}
$page = GetPageList($dtype, $keyword, "", $uid, "", $state, $pagesize, $cpage, "", "$fir-$sec-$thr", "mydoc", $doc_rewrite);

$navtitle = $navtype . ',' . $doc_name;
$metakeywords = $navtitle;
$metadescription = $navtitle;
if (checkmobile()) {
    $page = GetPageList_m($dtype, $keyword, "", $uid, "", $state, $pagesize, $cpage, "", "$fir-$sec-$thr", "mydoc", $doc_rewrite);
}

include template('doc:mydoc');
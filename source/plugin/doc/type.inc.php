<?php

/**
 *   @CopyRight   http://www.docswf.com
 *   @author      QQ50245077
 */
if (!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
    exit('Access Denied');
}

require './source/plugin/doc/function.php';
global $_G;
$doc = $_G['cache']['plugin']['doc'];
$doc_name = $doc['doc_name'];

if (isset($_GET['type']) && submitcheck('action')) {
    $type = $_GET['type'];
    $_G['charset'] == 'gbk' ? $type = iconv('utf-8', 'gbk', $type) : $type;
    if ($type) {
        $type = substr($type, 0, -1);
        UpdateDocType($type);
    }
    if ($_GET['update']) {
        $tip = lang('plugin/doc', 'type_updateok');
    }
}

if (isset($_GET['typeid']) && submitcheck('action')) {
    $typeid = $_GET['typeid'];
    DelType($typeid);
}

$navtitle = $doc_name;
$metakeywords = $doc_name;
$metadescription = $doc_name;

include template('doc:type');

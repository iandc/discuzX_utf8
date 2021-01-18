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
global $_G;
$doc = $_G['cache']['plugin']['doc'];

$doc_oss = $doc['doc_oss'];
$doc_oss_delfile = $doc['doc_oss_delfile'];
$doc_oss_fluxoss = $doc['doc_oss_fluxoss'];
if ($doc_oss) {
    require './source/plugin/doc/Oss/Oss.php';
}

$docid = $_GET['d'] == null ? -1 : $_GET['d'];

header('Content-type: image/png');
if ($docid != -1) {
    $path = GetViewDir($docid);
    $file = 'source/plugin/doc/' . $path . '1.png';
    if (echofile($file, true)) {
    } else {
        readfile('./source/plugin/doc/js/docread.png/4.png');
    }
}

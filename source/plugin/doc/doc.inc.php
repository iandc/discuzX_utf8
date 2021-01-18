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
$doc_welcome = $doc['doc_welcome'];
$doc_hometype = explode("|", $doc['doc_hometype']);
$doc_banner = $doc['doc_banner'];
$doc_ad = $doc['doc_ad'];
$doc_ad2 = $doc['doc_ad2'];
$doc_rewrite = $doc['doc_rewrite'];

$doc_readcredit = $doc['doc_readcredit'];
$doc_readcreditname = GetExtcreditesNameByID($doc_readcredit, $_G[setting][creditnames]);
$doc_downcredit = $doc['doc_downcredit'];
$doc_downcreditname = GetExtcreditesNameByID($doc_downcredit, $_G[setting][creditnames]);

$navtitle = $doc_name;
$metakeywords = $doc_name;
$metadescription = $doc_name . ',' . $doc_welcome;

include template('doc:doc');


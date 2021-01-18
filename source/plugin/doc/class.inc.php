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
$doc_rewrite = $doc['doc_rewrite'];
$navtitle = $doc_name;
$metakeywords = $doc_name;
$metadescription = $doc_name;
include template('doc:class');

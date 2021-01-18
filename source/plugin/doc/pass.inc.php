<?php

/**
 *   @CopyRight   http://www.docswf.com
 *   @author      QQ50245077
 */
if (!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
    exit('Access Denied');
}
require './source/plugin/doc/function.php';
require_once libfile('function/credit');
loadcache('plugin');
global $_G;
$doc = $_G['cache']['plugin']['doc'];
$doc_name = $doc['doc_name'];
//$doc_ad3 = $doc['doc_ad3'];
$doc_readcredit = $doc['doc_readcredit'];
$doc_readcreditname = GetExtcreditesNameByID($doc_readcredit, $_G[setting][creditnames]);
$doc_downcredit = $doc['doc_downcredit'];
$doc_downcreditname = GetExtcreditesNameByID($doc_downcredit, $_G[setting][creditnames]);
$doc_uploadcredit = $doc['doc_uploadcredit'];
$doc_uploadcreditname = GetExtcreditesNameByID($doc_uploadcredit, $_G[setting][creditnames]);
$doc_uploadcreditsize = $doc['doc_uploadcreditsize'];

$doc_yun = $doc['doc_yun'];
$doc_copy = $doc['doc_copy'];
$doc_h5 = 0;//$doc['doc_h5'];
$doc_ypage = $doc['doc_ypage'];

$doc_oss = $doc['doc_oss'];
if ($doc_oss) {
    require './source/plugin/doc/Oss/Oss.php';
}

if (isset($_GET['cs']) && submitcheck('action')) {
    if ($_GET['cs'] == "1") {
        $cid = $_GET['cid'] == null ? "0" : $_GET['cid'];
        ChangeDocState($cid, 1);
        $doc = GetDocInfo($cid); 
        _updatemembercount($doc[12], array($doc_uploadcredit => $doc_uploadcreditsize), true, '', $cid, lang('plugin/doc', 'credit_upload'), lang('plugin/doc', 'credit_upload'), lang('plugin/doc', 'credit_upload2'));  
        echo "1";
        exit(0);
    }
    if ($_GET['cs'] == "-1") {
        $cid = $_GET['cid'] == null ? "0" : $_GET['cid'];
        ChangeDocState($cid, -1);
        echo "1";
        exit(0);
    }
    if ($_GET['cs'] == "-2") {
        $cid = $_GET['cid'] == null ? "0" : $_GET['cid'];
        ChangeDocState($cid, -2);
        echo "1";
        exit(0);
    }
    if ($_GET['cs'] == "2") {
        $cid = $_GET['cid'] == null ? "0" : $_GET['cid'];
        IsImport($cid);
        echo "1";
        exit(0);
    }
	if ($_GET['cs'] == "c") {
        $cid = $_GET['cid'] == null ? "0" : $_GET['cid'];
    
		$DocInfo = GetDocInfo($cid);
		$docpath = substr($DocInfo[3], 18);
		$title = $DocInfo[5];
			
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
	
        echo "1";
        exit(0);
    }
}

$type = $_GET['t'] == null ? "1-0-0" : $_GET['t'];
$type = explode("-", $type);
$fir = $type[0];
$sec = $type[1];
$thr = $type[2];
$four = $type[3];
$dtype = 0;
if ($fir != 0) {
    $dtype = $fir;
}
if ($sec != 0) {
    $dtype = $sec;
}
if ($thr != 0) {
    $dtype = $thr;
}
if ($four != 0) {
    $dtype = $four;
}

$keyword = $_GET['k'] == null ? "" : $_GET['k'];
$state = $_GET['s'] == null ? "1" : $_GET['s'];
$uid = $_GET['u'] == null ? "" : $_GET['u'];
$cpage = $_GET['p'] == null ? 1 : $_GET['p'];
$pagesize = 10;
$page = GetPagePass($dtype, $keyword, "", $uid, "", $state, $pagesize, $cpage, "", "$fir-$sec-$thr");

$navtitle = $doc_name;
$metakeywords = $navtitle;
$metadescription = $navtitle;

include template('doc:pass');

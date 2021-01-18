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
$doc_name = $doc['doc_name'];
$doc_viewgroups = unserialize($doc['doc_viewgroups']);
$doc_downgroups = unserialize($doc['doc_downgroups']);
$doc_mangegroups = unserialize($doc['doc_mangegroups']);
$doc_vipgroups = unserialize($doc['doc_vipgroups']);
$doc_readcredit = $doc['doc_readcredit'];
$doc_readcreditname = GetExtcreditesNameByID($doc_readcredit, $_G[setting][creditnames]);
$doc_downcredit = $doc['doc_downcredit'];
$doc_downcreditname = GetExtcreditesNameByID($doc_downcredit, $_G[setting][creditnames]);
$doc_creditper = $doc['doc_creditper'];
$doc_docad = str_ireplace('"', "'", $doc["doc_docad"]);
$doc_rewrite = $doc['doc_rewrite'];
$doc_viprate = $doc['doc_viprate'];
$doc_h5 = 0;//$doc['doc_h5'];
$doc_copy = $doc['doc_copy'];

$doc_oss = $doc['doc_oss'];
$doc_oss_delfile = $doc['doc_oss_delfile'];
$doc_oss_fluxoss = $doc['doc_oss_fluxoss'];
if ($doc_oss) {
    require './source/plugin/doc/Oss/Oss.php';
}

$loginuserid = $_G['uid'];
$loginusername = $_G['username'];
$docid = $_GET['d'] == null ? 0 : $_GET['d'];
$cpage = $_GET['p'] == null ? 1 : $_GET['p'];
$pagesize = 10;
$date = date('Y-m-d', time());

if (!in_array($_G[groupid], $doc_viewgroups)) {
    if (!$_G['uid']) {
        showmessage(lang('plugin/doc', 'pleaselogin'), '', array(), array('login' => true));
        exit(0);
    } else {
        showmessage(lang('plugin/doc', 'norightview'), 'plugin.php?id=doc:doc');
        exit(0);
    }
}

if (submitcheck('action')) {
    if ($_GET['action'] == "insertcomment") {
        if ($loginuserid) {
            $star = $_GET['star'] ? $_GET['star'] : "5";
            $comment = $_GET['comment'];
            $_G['charset'] == 'gbk' ? $comment = iconv('utf-8', 'gbk', $comment) : $comment;
            $state = 0;
            $data_array = array(
                'comment' => sql_inject($comment),
                'star' => sql_inject($star),
                'uploaddate' => $date,
                'docid' => sql_inject($docid),
                'userid' => $loginuserid,
                'username' => $loginusername,
                'state' => $state,
            );
            DB::insert('ds_doccomment', $data_array);
            echo lang('plugin/doc', 'view_sendcommentok');
        } else {
            echo lang('plugin/doc', 'pleaselogin');
        }
        exit(0);
    }
    if ($_GET['action'] == "getcomment") {
        $comments = "";
        foreach (GetComment($docid, $pagesize, $cpage) as $Comment) {
            $comments .= "<div class=\"ds-user-comments-list\" commentid=\"$Comment[0]\">
                        <img class=\"ds-user-comments-upic\" src=\"uc_server/avatar.php?uid=$Comment[5]&size=small\"/>
                        <div class=\"ds-user-comments-h1\"><span width=\"$Comment[2]\" class=\"ds-user-comments-star\"></span><span class=\"ds-user-comments-user\">$Comment[6]</span><span class=\"ds-user-comments-del\">&times;</span></div>
                        <div class=\"ds-user-comments-text\">$Comment[1]</div>
                        <div class=\"ds-user-comments-time\">$Comment[3]</div>
                    </div>";
        }
        $commentpage = GetCommentPage($docid, $pagesize, $cpage);
        echo $comments . '|' . $commentpage;
        exit(0);
    }
    if ($_GET['action'] == "delcomment") {
        $commentid = $_GET['commentid'] ? $_GET['commentid'] : "";
        DelComment($commentid);
        echo lang('plugin/doc', 'view_commentdel');
        exit(0);
    }
    if ($_GET['action'] == "readdoc") {
        $readpay = $_GET['readpay'] ? $_GET['readpay'] : "0";
        $touid = $_GET['touid'] ? $_GET['touid'] : "1";
		
		if (in_array($_G[groupid], $doc_vipgroups)) {
			$readpay *= $doc_viprate;
			$downpay *= $doc_viprate;
		}

        if (IsPayRead($docid, $loginuserid)) {
            echo "1";
        } else if (IsMemberExtcredits($loginuserid, 'extcredits' . $doc_readcredit, $readpay)) {
            InsertLog("payread", $docid, $loginuserid, $loginusername);
            _updatemembercount($loginuserid, array($doc_readcredit => -$readpay), true, '', $docid, lang('plugin/doc', 'credit_payread'), lang('plugin/doc', 'credit_payread'), lang('plugin/doc', 'credit_payread2'));
            _updatemembercount($touid, array($doc_readcredit => $readpay - $readpay * $doc_creditper), true, '', $docid, lang('plugin/doc', 'credit_forread'), lang('plugin/doc', 'credit_forread'), lang('plugin/doc', 'credit_forread2'));

            echo "1";
        } else {
            echo "0";
        }
        exit(0);
    }
}

$delcomment = '';
if (in_array($_G[groupid], $doc_mangegroups)) {
    $delcomment = '<span class="ds-user-comments-del">&times;</span>';
}

$doc_star = GetCommentStar($docid) . 'px';
$doc_commentcount = GetCommentCount($docid);
SetDocViewCount($docid);
InsertLog('view', $docid, $loginuserid, $loginusername);
$DocInfo = GetDocInfo($docid);
$docexeten = $DocInfo[1];
$docdir = $DocInfo[2];
$docpath = $DocInfo[3];
$docsize = $DocInfo[4];
$title = $DocInfo[5];
$short = $DocInfo[6];
$doctype = $DocInfo[7];
$key = $DocInfo[8];
$freepage = (int) $DocInfo[9];
$readpay = $DocInfo[10];
$downpay = $DocInfo[11];
$userid = $DocInfo[12];
$username = $DocInfo[13];
$uploaddate = $DocInfo[14];
$viewcount = $DocInfo[15];
$downcount = $DocInfo[16];
$isimport = $DocInfo[17];
$pcount = $DocInfo[18];
$pages = $DocInfo[19];
if ($freepage <= -1 || $freepage >= $pcount || $freepage === null || IsPayRead($docid, $loginuserid) || $loginuserid == $userid) {
    $freepage = $pcount;
}
if (IsPayRead($docid, $loginuserid) || $loginuserid == $userid) {
    $readpay = 0;
}
if (IsDownLoad($docid, $loginuserid) || $loginuserid == $userid) {
    $downpay = 0;
}

$doc_percent = $DocInfo[19];

$lastdoc = GetDocInfo($docid-1);
$nextdoc = GetDocInfo($docid+1);

if ($doc_oss) {
    $page = GetDir($docpath) . 'pages';
    if (file_exists($page)) {
        SetDocPagePercent(file_get_contents($page), 0, substr($docpath, 18));
        unlink($page);
        synoss($docpath);
    }
}

$navtitle = $title . ',' . $doc_name;
$metakeywords = $key;
$metadescription = $short;

if (IS_ROBOT || isset($_GET['seo'])) {
    $spage = $_GET['ps'] == null ? 1 : $_GET['ps'];
    $file = $docdir . '1.png';
    if (!file_exists($file)) {
        $txt = "4";
    } else {
        $size = filesize($file);
        $fp = @fopen($file, "rb");
        if ($fp) {
            fseek($fp, $size - 5);
            $v = bin2hex(fread($fp, 1));
            for ($i = 0; $i < 4; $i++) {
                $a1 = fread($fp, 1);
                $a2 = bin2hex($a1) . $a2;
            }
        }
        fclose($fp);
        $id = base_convert($a2, 16, 10);
        if ($v . $id) {
            $pass = explode("|", CSee($v . $id));
            if ($pass[0] == "1" || $pass[0] == "2") {
                $file = $docdir . $spage . '.view';
                if (!file_exists($file)) {
                    $txt = "4";
                } else {
                    $fp = fopen($file, "rb");
                    if ($fp) {

                        $pass = explode(",", $pass[1]);
                        $b2 = '';
                        for ($i = 0; $i < 4; $i++) {
                            $b1 = fread($fp, 1);
                            $b2 = bin2hex($b1) . $b2;
                        }
                        $buf_length = base_convert($b2, 16, 10);

                        $data = fread($fp, $buf_length);
                        fclose($fp);

                        $sp_size = floor($buf_length / 32);
                        $p_extra = $buf_length - $sp_size * 32;
                        $data_dec = '';
                        for ($i = 0; $i < $sp_size; $i++) {
                            foreach ($pass as $p) {
                                $data_dec .= $data[$i * 32 + $p];
                            }
                        }
                        if ($p_extra > 0) {
                            $data_dec .= substr($data, -$p_extra);
                        }

                        if (strpos($data_dec, 'xml') === 5 || strpos($data_dec, 'xml') === 2) {
                            $txt = strip_tags($data_dec);
                        } else {

                        }

                    } else {
                        $txt = "4";
                    }
                }
            } else if ($pass[0] == "-1") {
                $txt = '-1';
            } else if ($pass[0] == "-2") {
                $txt = '-2';
            } else {
                $txt = '-4';
            }
        } else {
            $txt = "4";
        }
    }
    $_G['charset'] == 'gbk' ? $txt = iconv('utf-8', 'gbk//IGNORE', $txt) : $txt;
    if ($spage < $pcount) {
        $link = ++$spage;
    } else {
        $link = 1;
    }
    include template('doc:view_s');
} else {
    include template('doc:view');
}

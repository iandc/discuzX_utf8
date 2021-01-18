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
$doc_downcredit = $doc['doc_downcredit'];
$doc_creditper = $doc['doc_creditper'];
$doc_downgroups = unserialize($doc['doc_downgroups']);
$doc_vipgroups = unserialize($doc['doc_vipgroups']);
$doc_viprate = $doc['doc_viprate'];

$doc_oss = $doc['doc_oss'];
if ($doc_oss) {
    require './source/plugin/doc/Oss/Oss.php';
}

if (!in_array($_G[groupid], $doc_downgroups)) {
    showmessage(lang('plugin/doc', 'view_norightdowndoc'), 'plugin.php?id=doc');
    exit(0);
}

$docid = $_GET['d'] == null ? 0 : $_GET['d'];

if ($_G['uid']) {
    if ($docid) {
        SetDocDownCount($docid);
        $DocInfo = GetDocInfo($docid);
        $docexeten = $DocInfo[1];
        $docpath = $DocInfo[3];
        $docsize = $DocInfo[4];
        $title = $DocInfo[5];
        $downpay = $DocInfo[11];
        $userid = $DocInfo[12];
        if (in_array($_G[groupid], $doc_vipgroups)) {
            $downpay *= $doc_viprate;
        }
        if (echofile($docpath,false)) {
            if (IsDownLoad($docid, $_G['uid'])) {
                header('Content-Type:application/octet-stream');
                header('Content-Disposition:attachment;filename=' . $title . '.' . $docexeten);
                header('Content-Transfer-Encoding:binary');
                header('Content-Length:' . $docsize);
                echofile($docpath,true);
                @flush();
                @ob_flush();
                exit(0);
            } else if (IsMemberExtcredits($_G['uid'], 'extcredits' . $doc_downcredit, $downpay)) {
                InsertLog('down', $docid, $_G['uid'], $_G['username']);
                _updatemembercount($_G['uid'], array($doc_downcredit => -$downpay), true, '', $docid, lang('plugin/doc', 'credit_paydown'), lang('plugin/doc', 'credit_paydown'), lang('plugin/doc', 'credit_paydown2'));
                _updatemembercount($userid, array($doc_downcredit => $downpay - $downpay * $doc_creditper), true, '', $docid, lang('plugin/doc', 'credit_fordown'), lang('plugin/doc', 'credit_fordown'), lang('plugin/doc', 'credit_fordown2'));

                header('Content-Type:application/octet-stream');
                header('Content-Disposition:attachment;filename=' . $title . '.' . $docexeten);
                header('Content-Transfer-Encoding:binary');
                header('Content-Length:' .$docsize);
                echofile($docpath,true);
                @flush();
                @ob_flush();
                exit(0);
            } else {
                showmessage(lang('plugin/doc', 'extcreditsless'));
            }
        } else {
            showmessage(lang('plugin/doc', 'view_dochaslosed'));
        }
    }
} else {
    showmessage(lang('plugin/doc', 'pleaselogin'), array(), array('login' => true));
    exit(0);
}
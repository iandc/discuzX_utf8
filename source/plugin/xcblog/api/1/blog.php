<?php
if (!defined('IN_XCBLOG_API')) {
    exit('Access Denied');
}
require './source/class/class_core.php';
$discuz = C::app();
$discuz->init();
require_once XCBLOG_PLUGIN_PATH."/class/env.class.php";
$actionlist = array(
    'setbio' => array(),
    'setbg'  => array(),
	'resume' => array(),
    'side' => array(),
    'paperlist' => array(),
    'so' => array(),
);
$uid = $_G['uid'];
$username = $_G['username'];
$groupid = $_G["groupid"];
$action = isset($_GET['action']) ? $_GET['action'] : "get";
try {
    if (!isset($actionlist[$action])) {
        throw new Exception('unknow action');
    }
    $groups = $actionlist[$action];
    if (!empty($groups) && !in_array($groupid, $groups)) {
        throw new Exception('illegal request');
    }
    $res = $action();
    xcblog_env::result(array("data"=>$res));
} catch (Exception $e) {
    xcblog_env::result(array('retcode'=>100010,'retmsg'=>$e->getMessage()));
}
function setbio() 
{
    global $uid;
    $data = array (
        'bio' => xcblog_validate::getNCParameter('bio','bio','string',512),
    );
    return C::t('common_member_profile')->update($uid,$data);
}
function setbg()
{
    return 0; 
}
function resume()
{
    $return = array (
        'self_introduction' => array(), 
        'exp_education' => array(),     
        'exp_job' => array(),           
    );
    return $return;
}
function side() 
{
    $uid = xcblog_validate::getNCParameter('uid','uid','integer');
    $m_paper = C::m('#xcblog#xcblog_paper');
    $return = array(
        'catelist'  => $m_paper->get_categories($uid),
        'archives'  => $m_paper->get_archives($uid),
        'newpapers' => $m_paper->get_newpapers($uid),
    );  
    return $return;
}
function paperlist()
{
    $uid  = xcblog_validate::getNCParameter('uid','uid','integer');
    $page = xcblog_validate::getOPParameter('page','page','integer',1024,1);
    $return = C::m('#xcblog#xcblog_paper')->get_paperlist($uid,$page);
    $tids = array();
    foreach ($return['root'] as &$row) {
        $tids[] = $row['tid'];
    }
    $summaryMap = C::m('#xcblog#xcblog_forum')->get_thread_summary($tids);
    $forummap = C::m('#xcblog#xcblog_forum')->get_forum_map();
    foreach ($return['root'] as &$row) {
        $tid = $row['tid'];
        $summary = isset($summaryMap[$tid]) ? $summaryMap[$tid] : '';
        $row['summary'] = $summary;
        $row['catename'] = $forummap[$row['fid']]['name'];
    }
    return $return;
}
function so()
{
    $return = array(
        'root' => array(),
        'totalProperty' => 0,
        'annex' => lang('plugin/xcblog','all_paper'),
    );
    $uid  = xcblog_validate::getNCParameter('uid','uid','integer');
    $cateid = xcblog_validate::getOPParameter('cateid','cateid','integer',1024,0);
    $key = xcblog_validate::getOPParameter('key','key','string',128);
    $archive = xcblog_validate::getOPParameter('archive','archive','string',16,'');
    $start = xcblog_validate::getOPParameter('start','start','integer',1024,0);
    $limit = xcblog_validate::getOPParameter('limit','limit','integer',1024,20);
	$key = xcblog_utils::tocharset($key);
	$archive = xcblog_utils::tocharset($archive);
    $where = "authorid='$uid' AND closed=0 AND hidden=0 AND status=32 AND displayorder>=0";
    if ($key!='') {
        $where .= " AND subject like '%".$key."%'";
        $return['annex'] = lang('plugin/xcblog','search')." \"".$key."\"";
    }
    else if ($cateid!=0) {
        $forummap = C::m('#xcblog#xcblog_forum')->get_forum_map();
        if (!isset($forummap[$cateid])) {
            throw new Exception(lang('plugin/xcblog','paper_cate_lost'));
        }
        $return['annex'] = $forummap[$cateid]['name'];
        $where .= ' AND fid='.$cateid;
    }
    else if ($archive!='') {
        $return['annex'] = $archive;
        list($year,$month) = explode(lang('plugin/xcblog','year'),$archive);
        $where .= " AND DATE_FORMAT(FROM_UNIXTIME(dateline),'%Y')=$year";
        if ($month!='') {
            $map = array(
				lang('plugin/xcblog','January')   => '01',
				lang('plugin/xcblog','February')  => '02',
				lang('plugin/xcblog','March')     => '03',
				lang('plugin/xcblog','April')     => '04',
				lang('plugin/xcblog','May')       => '05',
				lang('plugin/xcblog','June')      => '06',
				lang('plugin/xcblog','July')      => '07',
				lang('plugin/xcblog','August')    => '08',
				lang('plugin/xcblog','September') => '09',
				lang('plugin/xcblog','October')   => '10',
				lang('plugin/xcblog','November')  => '11',
				lang('plugin/xcblog','December')  => '12',
            );
            $month = $map[$month];
            $where .= " AND DATE_FORMAT(FROM_UNIXTIME(dateline),'%m')=$month";
        }
    }
    $table_forum_thread = DB::table('forum_thread');
    $sql = <<<EOF
SELECT SQL_CALC_FOUND_ROWS tid,subject,dateline,views,replies 
FROM $table_forum_thread
WHERE $where 
ORDER BY dateline DESC
LIMIT $start,$limit
EOF;
    $return['root'] = DB::fetch_all($sql);
    $row = DB::fetch_first("SELECT FOUND_ROWS() AS total");
    $return['totalProperty'] = intval($row["total"]);
    $return['annex'].=" (".$return['totalProperty'].lang('plugin/xcblog','pian').")";
    return $return;
}
?>
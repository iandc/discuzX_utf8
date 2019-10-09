<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
class model_xcblog_paper
{
    private function effective_thread_condition($uid) {
        return "a.authorid='$uid' AND a.closed=0 AND a.hidden=0 AND a.status=32 AND a.displayorder>=0";
    }
    public function get_paperlist($uid,$page=1)
    {
        $return = array(
            'root' => array(),
            'page' => 1,
            'nextpage' => 1,
        );
        $setting = C::m('#xcblog#xcblog_setting')->get();
        $limit = $setting['home_paper_num'];
        if ($page<1) $page=1;
        $start = ($page-1) * $limit;
        $return['nextpage'] = $return['page'] = $page;
        $conditions = $this->effective_thread_condition($uid);
        $sql = <<<EOF
SELECT SQL_CALC_FOUND_ROWS tid,fid,subject,dateline,views,replies 
FROM %t as a
WHERE $conditions
ORDER BY dateline DESC
LIMIT $start,$limit
EOF;
        $return['root'] = DB::fetch_all($sql,array('forum_thread'));
        $row = DB::fetch_first("SELECT FOUND_ROWS() AS total");
        $totalProperty = intval($row["total"]);
        if ($totalProperty>($start+$limit)) {
            $return['nextpage'] = $return['page']+1;
        }
        return $return;
    }
    public function get_categories($uid)
    {
        $return = array();
        $table_forum_forum  = DB::table('forum_forum');
        $table_forum_thread = DB::table('forum_thread');
        $conditions = $this->effective_thread_condition($uid);
        $sql = <<<EOF
SELECT a.fid,b.name,count(1) as papernum
FROM $table_forum_thread as a 
LEFT JOIN $table_forum_forum as b ON a.fid=b.fid
WHERE $conditions
GROUP BY a.fid
ORDER BY a.fid ASC
EOF;
        $res = DB::fetch_all($sql);
        $return[] = array(
            'cateid' => 0,
            'catename' => lang('plugin/xcblog','all_paper'),
            'stat' => 0,
        );
        foreach ($res as &$row) {
            $return[] = array (
                'cateid'   => intval($row['fid']),
                'catename' => $row['name'],
                'stat'     => intval($row['papernum']),
            );
            $return[0]['stat'] += intval($row['papernum']);
        }
        return $return;
    }
    public function get_newpapers($uid)
    {
        $return = array();
        $conditions = $this->effective_thread_condition($uid);
        $sql = <<<EOF
SELECT tid,subject,dateline,views,replies 
FROM %t as a
WHERE $conditions
ORDER BY dateline DESC
LIMIT 0,10
EOF;
        return DB::fetch_all($sql,array('forum_thread'));
    }
    public function get_archives($uid)
    {
        $return = array();
        $table_forum_thread = DB::table('forum_thread');
        $conditions = $this->effective_thread_condition($uid);
        $sql = <<<EOF
SELECT 
DATE_FORMAT(FROM_UNIXTIME(dateline),'%Y') as dy,
DATE_FORMAT(FROM_UNIXTIME(dateline),'%m') as dm,
count(1) as papernum
FROM $table_forum_thread as a
WHERE $conditions
GROUP BY dy,dm
ORDER BY dateline DESC
EOF;
        $res = DB::fetch_all($sql);
        $curyear = date("Y");
        $nian = lang('plugin/xcblog','year');
        $monthmap = array(
            '01' => lang('plugin/xcblog','January'),
			'02' => lang('plugin/xcblog','February'),
			'03' => lang('plugin/xcblog','March'),
			'04' => lang('plugin/xcblog','April'),
			'05' => lang('plugin/xcblog','May'),
			'06' => lang('plugin/xcblog','June'),
			'07' => lang('plugin/xcblog','July'),
			'08' => lang('plugin/xcblog','August'),
			'09' => lang('plugin/xcblog','September'),
			'10' => lang('plugin/xcblog','October'),
			'11' => lang('plugin/xcblog','November'),
			'12' => lang('plugin/xcblog','December'),
        );
        $map = array();
        foreach ($res as &$row) {
            $dy = $row['dy'];
            $dm = $row['dm'];
            $stat = intval($row['papernum']);
            $key = $dy.$nian.$monthmap[$dm];
            if ($dy!=$curyear) {
                $key = $dy.$nian;
            }
            if (!isset($map[$key])) $map[$key] = 0;
            $map[$key] += $stat;
        }
        foreach ($map as $k => $v) {
            $return[] = array (
                'time' => $k,
                'stat' => $v,
            );
        }
        return $return;
    }
}
?>
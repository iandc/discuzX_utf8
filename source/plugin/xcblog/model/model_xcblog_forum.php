<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
class model_xcblog_forum
{
    public function get_forum_map()
    {
        global $_G;
        if(!isset($_G['cache']['forums'])) {
            loadcache('forums');
        }
        return $_G['cache']['forums'];
    }
    public function get_thread_summary(array &$tids,$maxlen=300)
    {
		global $_G;
		$charset = $_G['charset'];
        $map = array();
        if (empty($tids)) return $map;
        $table_forum_post = DB::table('forum_post');
        $ids = implode(',',$tids);
        $sql = <<<EOF
SELECT tid,message
FROM $table_forum_post
WHERE tid IN ($ids) AND first=1
EOF;
        $res = DB::fetch_all($sql);
        foreach ($res as &$row) {
            $message = $this->strip_all_tags($row['message']);
            if (mb_strlen($message,$charset) > $maxlen) {
                $message = mb_substr($message,0,$maxlen,$charset).'...';
            }
            $map[$row['tid']] = $message;
        }
        return $map;
    }
    public function strip_all_tags($message)
    {
        require_once libfile('class/bbcode');
        require_once libfile('function/discuzcode');
        $bbcode = new bbcode();
        $message = $bbcode->bbcode2html($message,1);
        $message = discuzcode($message);
        $message = preg_replace("/\[attach\](\d+)\[\/attach\]/i", '', $message);
        $message = preg_replace("/\[password\](.+)\[\/password\]/i", '', $message);
        $message = htmlspecialchars_decode($message);
        $message = preg_replace("/&nbsp;/i", '', $message);
        $message = strip_tags($message);
        return $message;
    }
}
?>
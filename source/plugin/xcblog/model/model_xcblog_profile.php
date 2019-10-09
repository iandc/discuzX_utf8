<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
class model_xcblog_profile
{
    public function getByUid($uid)
    {
        $table_common_member = DB::table('common_member');
        $table_common_member_profile = DB::table('common_member_profile');
        $sql = <<<EOF
SELECT a.username,a.email,b.*
FROM $table_common_member as a LEFT JOIN $table_common_member_profile as b ON a.uid=b.uid
WHERE a.uid=$uid
EOF;
        return DB::fetch_first($sql);
    }
}
?>
<?php
if (!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
    exit('Access Denied');
}

$sql = <<<EOF
DROP TABLE IF EXISTS pre_qxt_login_seccode;
DROP TABLE IF EXISTS pre_qxt_login_smslist;
DROP TABLE IF EXISTS pre_qxt_login_user;
EOF;
runquery($sql);

$finish = TRUE;
?>
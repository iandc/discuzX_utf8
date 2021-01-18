<?php

/**
 *   @CopyRight   http://www.docswf.com
 *   @author      QQ50245077
 */
if (!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
    exit('Access Denied');
}
$sql = <<<EOF
DROP TABLE IF EXISTS pre_ds_docinfo;
DROP TABLE IF EXISTS pre_ds_doctype;
DROP TABLE IF EXISTS pre_ds_doccomment;
DROP TABLE IF EXISTS pre_ds_doclog;
EOF;
runquery($sql);
$finish = TRUE;

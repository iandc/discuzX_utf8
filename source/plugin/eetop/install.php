<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = <<<EOF

DROP TABLE IF EXISTS pre_eetop;
CREATE TABLE cdb_eetop (
  `id` mediumint(8) unsigned NOT NULL,
  `ext` text NOT NULL DEFAULT '',
  `createTime` int(10) unsigned NOT NULL DEFAULT '0',
  `updateTime` int(10) unsigned NOT NULL DEFAULT '0',  
  PRIMARY KEY (`id`),
  KEY `updateTime` (`updateTime`)
) TYPE=MyISAM;

EOF;

runquery($sql);

$finish = TRUE;

?>
<?php
/*
 * 应用中心主页：https://addon.dismall.com/?@1552.developer
 * 人工智能实验室：Discuz!应用中心十大优秀开发者！
 * 插件定制 联系QQ594941227
 * From www.ailab.cn
 */
 
if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}
$filepath=DISCUZ_ROOT.'./data/sysdata/';
$handle=opendir($filepath); 
while(false!==($file=readdir($handle))){ 
	if(substr_count($file,'cache_nimba_forumgroup_')){
		@unlink($filepath.$file);
	}
}
$finish = TRUE;
?>
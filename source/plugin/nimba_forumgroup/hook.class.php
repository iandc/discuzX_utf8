<?php
/*
 * 应用中心主页：https://addon.dismall.com/?@1552.developer
 * 人工智能实验室：Discuz!应用中心十大优秀开发者！
 * 插件定制 联系QQ594941227
 * From www.ailab.cn
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class plugin_nimba_forumgroup {

}
class plugin_nimba_forumgroup_forum extends plugin_nimba_forumgroup {
	function index_middle_output(){
		global $_G,$forumlist;
		$gid=intval($_GET['gid']);
		if(!$gid) return '';
		if(!count($forumlist)) return '';
		$vars = $_G['cache']['plugin']['nimba_forumgroup'];
		$cache=intval($vars['cache']);
		$cachetime=intval($vars['cachetime']);
		$ad1=trim($vars['ad1']);
		$ad2=trim($vars['ad2']);
		$title=trim($vars['title']);
		$open=trim($vars['open']);
		$pagenum=trim($vars['pagenum']);
		$page=max(1,intval($_GET['page']));
		$forums=array_keys($forumlist);
		if($cache&&$cachetime&&$page==1){//只缓存访问量最大的首页
			$filepath=DISCUZ_ROOT.'./data/sysdata/cache_nimba_forumgroup_'.$gid.'.php';
			if(file_exists($filepath)){
				@require_once $filepath;
				if(TIMESTAMP-$lasttime<$cachetime){//缓存在有效期
					//free
					include template('nimba_forumgroup:threadlist');
					return $return;
				}
			}			
		}
		$count=DB::result_first("select count(*) from ".DB::table('forum_thread')." where fid in(".implode(',',$forums).") and displayorder>=0 ");
		$threadlist=DB::fetch_all("select * from ".DB::table('forum_thread')." where fid in(".implode(',',$forums).") and displayorder>=0 order by lastpost desc limit ".($pagenum*($page-1)).",$pagenum");	
		//free
		if($cache&&$cachetime&&$page==1){//只缓存访问量最大的首页 创建或更新缓存
			@require_once libfile('function/cache');
			$cacheArray = "\$count=".$count.";\n";
			$cacheArray .= "\$threadlist=".arrayeval($threadlist).";\n";
			$cacheArray .= "\$lasttime=".TIMESTAMP.";\n";
			writetocache('nimba_forumgroup_'.$gid,$cacheArray);
		}
		include template('nimba_forumgroup:threadlist');
		return $return;	
	}
}

?>
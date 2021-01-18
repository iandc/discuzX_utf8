<?php
/*
 * 主页：https://addon.dismall.com/?@1552.developer
 * 人工智能实验室：Discuz!应用中心十大优秀开发者！
 * 插件定制 联系QQ594941227
 * From www.ailab.cn
 */
 
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
function _get_sitemap($filename,$web_root,$cycle,$charset,$notin,$show,$open,$num,$maxmaps,$page='',$htmlmade=0,$https=false,$xmldir=1){
	global $_G;
	//$web_root 网站目录
	if(!$web_root){
		$web_root = substr($_G['PHP_SELF'], 0, strrpos($_G['PHP_SELF'], '/'));
		$web_root.='/';
	}	
	require_once DISCUZ_ROOT.'./source/discuz_version.php';
	if(strtolower(substr(DISCUZ_VERSION,0,2))=='x2'||strtolower(substr(DISCUZ_VERSION,0,2))=='x1') $htmlmade=0;//低版本不支持
	$maps=array();
	//开始统计各类网址数量
	if($show[1]==1) $threadnum=DB::result_first("SELECT count(*) FROM ".DB::table('forum_thread')." a inner join ".DB::table('forum_forum')." b on a.fid=b.fid where a.displayorder!=-2 and a.displayorder!=-1 $notin ORDER BY a.tid DESC");
	else $threadnum=0;
	if($show[2]==1) $forumnum=DB::result_first("SELECT count(*) FROM ".DB::table('forum_forum')." a where a.type='forum' and status=1 $notin ORDER BY a.fid DESC");
	else $forumnum=0;
	if($show[3]==1) $articlenum=DB::result_first("SELECT count(*) FROM ".DB::table('portal_article_title')." ORDER BY aid DESC");
	else $articlenum=0;
	$num=empty($num)? 10000:$num;//分卷网址数量
	$page=max(1,intval($page));
	$startnum=($page-1)*$num;
	$endnum=$startnum+$num;
	if($show[0]==1&&$page==1){
		$maps_0=get_siteurl();
		$endnum-=1;
	}else{
		$maps_0=array();
	}
	if($threadnum) $maps_1=get_thread($web_root,$cycle,$notin,$startnum,$num);
	else $maps_1=array();
	$remain=$num-count($maps_1);
	if($forumnum&&$remain>0){
		$startnum=$startnum-$threadnum+count($maps_1);
		$startnum=max(0,$startnum);
		$maps_2=get_forum($web_root,$cycle,$notin,$startnum,$remain);
	}else{
		$maps_2=array();
	}
	$remain=$remain-count($maps_2);
	if($articlenum&&$remain>0){
		$startnum=$startnum-$forumnum+count($maps_2);
		$startnum=max(0,$startnum);
		$maps_3=get_article($web_root,$cycle,$notin,$startnum,$remain,$htmlmade);
	}else{
		$maps_3=array();
	}
	$maps=array_merge($maps_0,$maps_1,$maps_2,$maps_3);
	$start="<?xml version=\"1.0\" encoding=\"$charset\"?>\n";
	$start.="<urlset\n";
	$start.="xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\"\n";
	$start.="xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"\n";
	$start.="xsi:schemaLocation=\"http://www.sitemaps.org/schemas/sitemap/0.9\n";
	$start.="http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd\">\n";
	$end="</urlset>\n";
 	if($open==0){//不分卷
 		$sitemap='';
		foreach($maps as $k=>$map){
			$sitemap.="<url>\n";
			$sitemap.="<loc>".$map['link']."</loc>\n";
			$sitemap.="<priority>".$map['priority']."</priority>\n";
			$sitemap.="<lastmod>".$map['riqi']."</lastmod>\n";
			$sitemap.="<changefreq>".$map['cycle']."</changefreq>\n";
			$sitemap.="</url>\n";
		}
		if($https){
			$sitemap=str_replace('http://','https://',$sitemap);
		}		
		$sitemap=$start.$sitemap.$end;
		$fp = fopen(DISCUZ_ROOT.($xmldir==2? '/data/':'/').$filename.'.xml','w');
		fwrite($fp,$sitemap);
		fclose($fp);
		return 1;
	}else{//分卷
		if(!empty($page)&&(count($maps)>0)&&($maxmaps==0||$maxmaps>=$page)){
			$sitemap='';
			$name=$page>1? $filename.'_'.$page:$filename;
 			foreach($maps as $k=>$map){
				$sitemap.="<url>\n";
				$sitemap.="<loc>".$map['link']."</loc>\n";
				$sitemap.="<lastmod>".$map['riqi']."</lastmod>\n";
				$sitemap.="<changefreq>".$map['cycle']."</changefreq>\n";
				$sitemap.="<priority>".$map['priority']."</priority>\n";				
				$sitemap.="</url>\n";
			}
			if($https){
				$sitemap=str_replace('http://','https://',$sitemap);
			}	
			$sitemap=str_replace('http://http://','http://',$sitemap);
			$sitemap=str_replace('https://https://','https://',$sitemap);
			$sitemap=$start.$sitemap.$end;
			$fp = fopen(DISCUZ_ROOT.($xmldir==2? '/data/':'/').$name.'.xml','w');
			fwrite($fp,$sitemap);
			fclose($fp);
			return 0;
		}else return $page-1;
	}
}
function get_siteurl(){
	global $_G;
	loadcache('plugin');
	$rank = $_G['cache']['plugin']['nimba_sitemap']['rank0'];
	$rank=empty($rank)? '1.0':$rank;
	$base=array('weekly','always','hourly','daily','weekly','monthly','yearly','never');
	$cycle=$base[intval($_G['cache']['plugin']['nimba_sitemap']['change0'])];
	$link=$_G['siteurl'];
	$riqi=dgmdate(TIMESTAMP,'Y-m-d');
	$map=array('link'=>$link,'priority'=>$rank,'riqi'=>$riqi,'cycle'=>$cycle);
	$maps[]=$map;
	return $maps;
}	
function get_thread($web_root,$cycle,$notin,$startnum,$num){
	global $_G;
	loadcache('plugin');			
	$rank = $_G['cache']['plugin']['nimba_sitemap']['rank1'];
	$rank=empty($rank)? 0.8:$rank;
	$base=array('weekly','always','hourly','daily','weekly','monthly','yearly','never');
	$cycle=$base[intval($_G['cache']['plugin']['nimba_sitemap']['change1'])];
	$isrewrite=_isrewrite('forum_viewthread');	
	$subdomain=subdomain('forum');
	$maps=array();
	$querys = DB::query("SELECT a.fid,a.tid,a.lastpost FROM ".DB::table('forum_thread')." a inner join ".DB::table('forum_forum')." b on a.fid=b.fid where a.displayorder>=0 $notin ORDER BY a.tid DESC  LIMIT $startnum,$num");
	while($threadfid = DB::fetch($querys)){
		$forumDomain=_getForumDomain($threadfid['fid']);
		if(!$forumDomain) $forumDomain=$subdomain;
		if($_G['setting']['forumkeys'][$threadfid['fid']]){//版块别名
			$threadfid['fid']=$_G['setting']['forumkeys'][$threadfid['fid']];
		}
		if(!empty($isrewrite)) $link='http://'.$forumDomain.$web_root.str_replace(array('{fid}','{tid}','{page}','{prevpage}'),array($threadfid['fid'],$threadfid['tid'],1,1),$isrewrite);//静态规则
		else $link='http://'.$forumDomain.$web_root.'forum.php?mod=viewthread&amp;tid='.$threadfid['tid'];//动态规则,xml中&要换成&amp;
		$riqi=dgmdate($threadfid['lastpost'],'Y-m-d');
		$map=array('link'=>$link,'priority'=>$rank,'riqi'=>$riqi,'cycle'=>$cycle);
		$maps[]=$map;
	}
	return $maps;
}

function get_forum($web_root,$cycle,$notin,$startnum,$num){
	global $_G;
	loadcache('plugin');			
	$rank = $_G['cache']['plugin']['nimba_sitemap']['rank2'];
	$rank=empty($rank)? 0.8:$rank;
	$base=array('weekly','always','hourly','daily','weekly','monthly','yearly','never');
	$cycle=$base[intval($_G['cache']['plugin']['nimba_sitemap']['change2'])];
	$isrewrite=_isrewrite('forum_forumdisplay');
	$subdomain=subdomain('forum');
	$maps=array();
	$querys = DB::query("SELECT a.fid,a.domain FROM ".DB::table('forum_forum')." a where a.type='forum' and status=1 $notin ORDER BY a.fid DESC  LIMIT $startnum,$num");
	while($threadfid = DB::fetch($querys)){
		if($threadfid['domain']&&$_G['setting']['domain']['root']['forum']) $subdomain=$threadfid['domain'].'.'.$_G['setting']['domain']['root']['forum'];//版块域名
		if(!empty($_G['setting']['forumkeys'][$threadfid['fid']])) $threadfid['fid']= $_G['setting']['forumkeys'][$threadfid['fid']];//板块别名
		if(!empty($isrewrite)) $link='http://'.$subdomain.$web_root.str_replace(array('{fid}','{page}'),array($threadfid['fid'],1),$isrewrite);//静态规则
		else $link='http://'.$subdomain.$web_root.'forum.php?mod=forumdisplay&amp;fid='.$threadfid['fid'];//动态规则,xml中&要换成&amp;
		$riqi=dgmdate(TIMESTAMP,'Y-m-d');
		$map=array('link'=>$link,'priority'=>$rank,'riqi'=>$riqi,'cycle'=>$cycle);
		$maps[]=$map;
	}
	return $maps;
}

function get_article($web_root,$cycle,$notin,$startnum,$num,$htmlmade=0){
	global $_G;
	loadcache('plugin');			
	$rank = $_G['cache']['plugin']['nimba_sitemap']['rank3'];
	$rank=empty($rank)? 0.8:$rank;
	$base=array('weekly','always','hourly','daily','weekly','monthly','yearly','never');
	$cycle=$base[intval($_G['cache']['plugin']['nimba_sitemap']['change3'])];	
	$isrewrite=_isrewrite('portal_article');
	$subdomain=subdomain('portal');
	$maps=array();
	if($htmlmade) $querys = DB::query("SELECT aid,dateline,htmlmade,htmlname,htmldir FROM ".DB::table('portal_article_title')." where status=0 ORDER BY aid DESC  LIMIT $startnum,$num");
	else $querys = DB::query("SELECT aid,dateline FROM ".DB::table('portal_article_title')." where status=0 ORDER BY aid DESC  LIMIT $startnum,$num");
	while($threadfid = DB::fetch($querys)){
		if($htmlmade&&$threadfid['htmlmade']){
			$link='http://'.$subdomain.$web_root.$threadfid['htmldir'].$threadfid['htmlname'].'.html';//HTML静态生成
		}else{
			if(!empty($isrewrite)) $link='http://'.$subdomain.$web_root.str_replace(array('{id}','{page}'),array($threadfid['aid'],1),$isrewrite);//静态规则
			else $link='http://'.$subdomain.$web_root.'portal.php?mod=view&amp;aid='.$threadfid['aid'];//动态规则,xml中&要换成&amp;
		}
		$riqi=dgmdate($threadfid['dateline'],'Y-m-d');
		$map=array('link'=>$link,'priority'=>$rank,'riqi'=>$riqi,'cycle'=>$cycle);
		$maps[]=$map;
	}
	return $maps;
}

function sitemapIndex($num,$filename,$xmldir){
	global $_G;
	$index='';
	$head="<?xml version=\"1.0\"  encoding=\"UTF-8\"?>\r\n<sitemapindex xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\r\n";
	$temp="<sitemap>\n<loc>%s</loc>\n<lastmod>%s</lastmod>\n</sitemap>\n";
	$end="</sitemapindex>";
	$riqi=dgmdate(TIMESTAMP,'Y-m-d');
	for($i=1;$i<=$num;$i++){
		$index.=empty($index)? sprintf($temp,$_G['siteurl'].($xmldir==2? 'data/':'').$filename.'.xml',$riqi):"\n".sprintf($temp,$_G['siteurl'].($xmldir==2? 'data/':'').$filename.'_'.$i.'.xml',$riqi);
	}
	$index=$head.$index.$end;
	$fp = fopen(DISCUZ_ROOT.($xmldir==2? '/data/':'/').$filename.'_index.xml','w');
	fwrite($fp,$index);
	fclose($fp);
}

//组织参数
loadcache('plugin');
$var = $_G['cache']['plugin']['nimba_sitemap'];
$auto =$var['auto'];
$open =$var['open'];
$htmlmade =$var['htmlmade'];
$xmldir =$var['xmldir'];
$https =$var['https'];
$num=$var['num'];
$filename =str_replace('.xml','',trim($var['filename']));
$web_root=trim($var['web_root']);
$url=empty($var['mysite']) ? $_G['siteurl']:$var['mysite'];
$date=trim($var['cycle']);
$charset='utf-8';
$ban=unserialize($var['ban']);
if(count($ban)==0) $notin='';
else $notin='and a.fid not in('.dimplode($ban).')';
$show=array(0,0,0,0);
$urls=unserialize($var['urls']);
if(in_array('1',$urls)) $show[0]=1;	
if(in_array('2',$urls)) $show[1]=1;	
if(in_array('3',$urls)) $show[2]=1;	
if(in_array('4',$urls)) $show[3]=1;	
$cycle='weekly';
$maxmaps=empty($var['maxmaps'])? 0:$var['maxmaps'];
//记录更新时间
$last=time();
@require_once libfile('function/cache');
$cacheArray .= "\$last=".$last.";\n";
writetocache('nimba_sitemap_log', $cacheArray);	
//更新提示
$page=max(0,intval($_GET['page']));
if($page==0){
	showtableheader(lang('plugin/nimba_sitemap','tips'));
	showtablerow('',array('colspan="9" class="tipsblock"'), array(lang('plugin/nimba_sitemap','going_6')));
	$page++;
	echo "<script>window.location.href='".ADMINSCRIPT."?action=plugins&operation=config&do=".$pluginid."&identifier=nimba_sitemap&pmod=update&page=$page';</script>";
}else{
	$sum=_get_sitemap($filename,$web_root,$cycle,$charset,$notin,$show,$open,$num,$maxmaps,$page,$htmlmade,$https,$xmldir);//继续生成地图
	if($sum==0){
		$color=array('red','blue');
		showtableheader(lang('plugin/nimba_sitemap','tips'));
		showtablerow('',array('colspan="9" class="tipsblock"'), array(lang('plugin/nimba_sitemap','page_doing',array(
			'color'=>$color[$page%2],
			'page'=>$page,
			'adminurl'=>ADMINSCRIPT,
			'pluginid'=>$pluginid,
			'nextpage'=>$page+1,
		))));
		$page++;
		echo "<script>window.location.href='".ADMINSCRIPT."?action=plugins&operation=config&do=".$pluginid."&identifier=nimba_sitemap&pmod=update&page=$page';</script>";
	}else{
		//返回结果
		
		$xmls=='';
		for($i=1;$i<=$sum;$i++){
			if($i==1) $xmls.='<br>'.$_G['siteurl'].($xmldir==2? 'data/':'').$filename.'.xml';
			else $xmls.='<br>'.$_G['siteurl'].($xmldir==2? 'data/':'').$filename.'_'.$i.'.xml';
		}
		showtableheader(lang('plugin/nimba_sitemap','tips'));
		showtablerow('',array('colspan="9" class="tipsblock"'), array(lang('plugin/nimba_sitemap','page_result',array(
			'page_time'=>dgmdate(TIMESTAMP,'Y-m-d H:i:s'),
			'xmls'=>$xmls,
		))));
		if($sum>1){//地图索引
			sitemapIndex($sum,$filename,$xmldir);
			showtablerow('',array('colspan="9" class="tipsblock"'), array(lang('plugin/nimba_sitemap','page_result_index',array(
				'index'=>$_G['siteurl'].($xmldir==2? 'data/':'').$filename.'_index.xml',
			))));		
		}
		showtableheader(lang('plugin/nimba_sitemap','page_help_title'));
		showtablerow('',array('colspan="9" class="tipsblock"'), array(lang('plugin/nimba_sitemap','page_help_list',array(
			'robots'=>$_G['siteurl'].($xmldir==2? 'data/':'').$filename.'.xml',
		))));
		//清理冗余
		$sum+=1;
		while(file_exists(DISCUZ_ROOT.($xmldir==2? '/data/':'/').$filename.'_'.$sum.'.xml')){
			unlink(DISCUZ_ROOT.($xmldir==2? '/data/':'/').$filename.'_'.$sum.'.xml');
			$sum+=1;
		}
	}
}
?>
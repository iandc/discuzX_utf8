<?php
/*
 * ��ҳ��https://addon.dismall.com/?@1552.developer
 * �˹�����ʵ���ң�Discuz!Ӧ������ʮ�����㿪���ߣ�
 * ������� ��ϵQQ594941227
 * From www.ailab.cn
 */
 
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
ini_set('memory_limit', '500M');
set_time_limit (0);
function sitemap_auto(){
	//��֯����
	global $_G;
	loadcache('plugin');			
	$var = $_G['cache']['plugin']['nimba_sitemap'];
	$open =$var['open'];
    $xmldir =$var['xmldir'];
    $https =$var['https'];
	$htmlmade =$var['htmlmade'];
	$num=$var['num'];
	$filename =str_replace('.xml','',trim($var['filename']));
	$web_root=trim($var['web_root']);
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
	//��ʼ��¼����ʱ��
	$last=time();
	@require_once libfile('function/cache');
	$cacheArray .= "\$last=".$last.";\n";
	writetocache('nimba_sitemap_log', $cacheArray);	
	//���µ�ͼ
	get_sitemap($filename,$web_root,$cycle,$charset,$notin,$show,$open,$num,$htmlmade,$https,$xmldir);//���ɵ�ͼ
	return '1';//����ֵ����������
}//��ȡ�ϴθ���ʱ�䲢�Զ�����
	 
function _isrewrite($item){
	global $_G;
	/*
	portal_topic
	portal_article
	forum_forumdisplay
	forum_viewthread
	group_group
	home_space
	home_blog
	forum_archiver
	*/
	$rewritestatus = $_G['setting']['rewritestatus'];
	$rewriterule = $_G['setting']['rewriterule'];
	//���������ǰ̨��ȡ�����������ݿ��ж�ȡ
	if(!$rewritestatus) $rewritestatus=C::t('common_setting')->fetch('rewritestatus',true);
	if(!$rewriterule) $rewriterule=C::t('common_setting')->fetch('rewriterule',true);
	if(in_array($item,$rewritestatus)&&$rewriterule[$item]){
		return $rewriterule[$item];
	}else{
		return false;
	}	
}
//echo _isrewrite('forum_viewthread');	

function subdomain($item){//��ѯ��̨���õ�Ӧ������
	global $_G;
	/*	
	portal
	forum
	group
	home
	mobile
	default
	*/
	
	$url =trim($_G['cache']['plugin']['nimba_sitemap']['mysite']);
	$domain = $_G['setting']['domain'];
	if($domain['app'][$item]){
		$return = $domain['app'][$item];
	}else{
		$return = $domain['app']['default'];
	}
	if(empty($return)){
		$return =$url;
	}
	return $return;	
}

function _getForumDomain($fid){
	@include DISCUZ_ROOT.'./data/sysdata/cache_domain.php';
	foreach($domain['list'] as $url=>$info){
		if($info['id']==$fid&&$info['idtype']=='forum'){
			return $url;
			break;
		}
	}
	return '';
}

function get_sitemap($filename,$web_root,$cycle,$charset,$notin,$show,$open,$num,$htmlmade=0,$https=false){
	global $_G;
	require_once DISCUZ_ROOT.'./source/discuz_version.php';
	if(strtolower(substr(DISCUZ_VERSION,0,2))=='x2'||strtolower(substr(DISCUZ_VERSION,0,2))=='x1') $htmlmade=0;//�Ͱ汾��֧��	
	loadcache('plugin');
	$base=array('weekly','always','hourly','daily','weekly','monthly','yearly','never');
	//$web_root ��վĿ¼
	if(!$web_root){
		$web_root = substr($_G['PHP_SELF'], 0, strrpos($_G['PHP_SELF'], '/'));
		$web_root.='/';
	}	
	$urlsum=array(0,0,0);
	$maps=array();
	if(file_exists(DISCUZ_ROOT.'source/plugin/nimba_sitemap/sitemap.vip.php')){
		$num=intval($num);
	}else{
		$num=min(10000,intval($num));
	}
	/***********************************************************************************************/
	//��վ��ͼsitemap.xml
	$start="<?xml version=\"1.0\" encoding=\"$charset\"?>\n";
	$start.="<urlset\n";
	$start.="xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\"\n";
	$start.="xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"\n";
	$start.="xsi:schemaLocation=\"http://www.sitemaps.org/schemas/sitemap/0.9\n";
	$start.="http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd\">\n";
	$end="</urlset>\n";
	
	if($show[0]==1){//��վ��ҳ
		$rank = $_G['cache']['plugin']['nimba_sitemap']['rank0'];
		$rank=empty($rank)? '1.0':$rank;
		$cycle=$base[intval($_G['cache']['plugin']['nimba_sitemap']['change0'])];
		$link=$_G['siteurl'];
		$riqi=dgmdate(TIMESTAMP,'Y-m-d');
		$map=array('link'=>$link,'priority'=>$rank,'riqi'=>$riqi,'cycle'=>$cycle);
		$maps[]=$map;
	}	

	if($show[1]==1){//��̳����
		$rank = $_G['cache']['plugin']['nimba_sitemap']['rank1'];
		$rank=empty($rank)? 0.8:$rank;
		$cycle=$base[intval($_G['cache']['plugin']['nimba_sitemap']['change1'])];
		$querys = DB::query("SELECT a.fid,a.tid,a.lastpost FROM ".DB::table('forum_thread')." a inner join ".DB::table('forum_forum')." b on a.fid=b.fid where a.displayorder>=0 $notin ORDER BY a.tid DESC  LIMIT 0,$num");
		$isrewrite=_isrewrite('forum_viewthread');
		$subdomain=subdomain('forum');
		while($threadfid = DB::fetch($querys)){
			if($urlsum[0]>=$num) break;
			$forumDomain=_getForumDomain($threadfid['fid']);
			if(!$forumDomain) $forumDomain=$subdomain;	
			if($_G['setting']['forumkeys'][$threadfid['fid']]){//������
				$threadfid['fid']=$_G['setting']['forumkeys'][$threadfid['fid']];
			}			
			if($isrewrite) $link='http://'.$forumDomain.$web_root.str_replace(array('{fid}','{tid}','{page}','{prevpage}'),array($threadfid['fid'],$threadfid['tid'],1,1),$isrewrite);//��̬����
			else $link='http://'.$forumDomain.$web_root.'forum.php?mod=viewthread&amp;tid='.$threadfid['tid'];//��̬����,xml��&Ҫ����&amp;
			$riqi=dgmdate($threadfid['lastpost'],'Y-m-d');
			$map=array('link'=>$link,'priority'=>$rank,'riqi'=>$riqi,'cycle'=>$cycle);
			$maps[]=$map;
			$urlsum[0]++;
		}
	}	

	if($show[2]==1){//��̳���
		$rank = $_G['cache']['plugin']['nimba_sitemap']['rank2'];
		$rank=empty($rank)? 0.8:$rank;	
		$cycle=$base[intval($_G['cache']['plugin']['nimba_sitemap']['change2'])];
		$isrewrite=_isrewrite('forum_forumdisplay');
		$subdomain=subdomain('forum');
		$querys = DB::query("SELECT a.fid,a.domain FROM ".DB::table('forum_forum')." a where a.type='forum' and status=1 $notin ORDER BY a.fid DESC  LIMIT 0,$num");
		while($threadfid = DB::fetch($querys)){
			if($urlsum[1]+$urlsum[0]>=$num) break;
			if($threadfid['domain']&&$_G['setting']['domain']['root']['forum']) $subdomain=$threadfid['domain'].'.'.$_G['setting']['domain']['root']['forum'];//�������
			if(!empty($_G['setting']['forumkeys'][$threadfid['fid']])) $threadfid['fid']= $_G['setting']['forumkeys'][$threadfid['fid']];//������
			if($isrewrite) $link='http://'.$subdomain.$web_root.str_replace(array('{fid}','{page}'),array($threadfid['fid'],1),$isrewrite);//��̬����
			else $link='http://'.$subdomain.$web_root.'forum.php?mod=forumdisplay&amp;fid='.$threadfid['fid'];//��̬����,xml��&Ҫ����&amp;
			$riqi=dgmdate(TIMESTAMP,'Y-m-d');
			$map=array('link'=>$link,'priority'=>$rank,'riqi'=>$riqi,'cycle'=>$cycle);
			$maps[]=$map;
			$urlsum[1]++;
		}
	}

	if($show[3]==1){//�Ż�����
		$rank = $_G['cache']['plugin']['nimba_sitemap']['rank3'];
		$rank=empty($rank)? 0.8:$rank;	
		$cycle=$base[intval($_G['cache']['plugin']['nimba_sitemap']['change3'])];
		$isrewrite=_isrewrite('portal_article');
		$subdomain=subdomain('portal');
		if($htmlmade) $querys = DB::query("SELECT aid,dateline,htmlmade,htmlname,htmldir FROM ".DB::table('portal_article_title')." where status=0 ORDER BY aid DESC  LIMIT 0,$num");
		else $querys = DB::query("SELECT aid,dateline FROM ".DB::table('portal_article_title')." where status=0 ORDER BY aid DESC  LIMIT 0,$num");//�Ͱ汾
		while($threadfid = DB::fetch($querys)){
			if($urlsum[2]+$urlsum[1]+$urlsum[0]>=$num) break;
			if($htmlmade&&$threadfid['htmlmade']){
				$link='http://'.$subdomain.$web_root.$threadfid['htmldir'].$threadfid['htmlname'].'.html';//HTML��̬����
			}else{			
				if($isrewrite) $link='http://'.$subdomain.$web_root.str_replace(array('{id}','{page}'),array($threadfid['aid'],1),$isrewrite);//��̬����
				else $link='http://'.$subdomain.$web_root.'portal.php?mod=view&amp;aid='.$threadfid['aid'];//��̬����,xml��&Ҫ����&amp;
			}
			$riqi=dgmdate($threadfid['dateline'],'Y-m-d');
			$map=array('link'=>$link,'priority'=>$rank,'riqi'=>$riqi,'cycle'=>$cycle);
			$maps[]=$map;
			$urlsum[2]++;
		}
	}
 	$sitemap='';
	
	if(count($maps)>$num) $maps=array_slice($maps,0,$num);
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

	$fp = fopen(DISCUZ_ROOT.($xmldir==2? '/data/':'/').$filename.'.xml','w');
	fwrite($fp,$sitemap);
	fclose($fp);
	return $urlsum;
}
?>
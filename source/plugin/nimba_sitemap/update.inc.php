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
global $plugin;
loadcache('plugin');
include 'libs/sitemap.dev.php';
if(substr($plugin['version'],-1,1)==2&&file_exists(DISCUZ_ROOT.'source/plugin/nimba_sitemap/libs/sitemap.vip.php')){
	//商业版
	include 'libs/sitemap.vip.php';	
}else{
	//免费版
	//开始组织参数
	loadcache('plugin');
	$var = $_G['cache']['plugin']['nimba_sitemap'];
    $auto =$var['auto'];
    $xmldir =$var['xmldir'];
    $https =$var['https'];
	$htmlmade =$var['htmlmade'];
	$filename =str_replace('.xml','',trim($var['filename']));
	$web_root=trim($var['web_root']);
	$url=empty($var['mysite']) ? $_G['siteurl']:$var['mysite'];
	$date=trim($var['cycle']);
	$num=$var['num'];
	$open =$var['open'];
	$charset='utf-8';
	$ban=unserialize($var['ban']);
	if(count($ban)==0) $notin='';
	else $notin='and a.fid not in('.dimplode($ban).')';
	$show=array(0,0,0);
	$urls=unserialize($var['urls']);
	if(in_array('1',$urls)) $show[0]=1;	
	if(in_array('2',$urls)) $show[1]=1;	
	if(in_array('3',$urls)) $show[2]=1;	
	if(in_array('4',$urls)) $show[3]=1;	
	$cycle='weekly';
	//记录更新时间
	$last=time();
	@require_once libfile('function/cache');
	$cacheArray .= "\$last=".$last.";\n";
	writetocache('nimba_sitemap_log', $cacheArray);	
    //更新提示
    $urlsum=get_sitemap($filename,$web_root,$cycle,$charset,$notin,$show,$open,$num,$htmlmade,$https);//生成地图 $open分卷参数无效 免费版
	showtableheader(lang('plugin/nimba_sitemap','tips'));
	showtablerow('',array('colspan="9" class="tipsblock"'), array(lang('plugin/nimba_sitemap','page_dev',array(
		'urlsum_0'=>$urlsum[0],
		'urlsum_1'=>$urlsum[1],
		'urlsum_2'=>$urlsum[2],
		'page_time'=>dgmdate(TIMESTAMP,'Y-m-d H:i:s'),
		'xmls'=>$_G['siteurl'].($xmldir==2? 'data/':'').$filename.'.xml',
	))));
	showtableheader(lang('plugin/nimba_sitemap','page_help_dev_title'));
	showtablerow('',array('colspan="9" class="tipsblock"'), array(lang('plugin/nimba_sitemap','page_help_dev_list')));	
	showtableheader(lang('plugin/nimba_sitemap','page_help_title'));
	showtablerow('',array('colspan="9" class="tipsblock"'), array(lang('plugin/nimba_sitemap','page_help_list',array(
		'robots'=>$_G['siteurl'].($xmldir==2? 'data/':'').$filename.'.xml',
	))));	
}
?>
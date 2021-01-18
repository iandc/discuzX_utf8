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

function RP_downloadpic($src,$localurl){
	if(!(file_exists($localurl)||copy($src,$localurl))){
		if(!$content=RP_baiduSpider($src)){
			$content=file_get_contents($src);
		}
		$fp=fopen($localurl,"w");
		fwrite($fp,$content);
		fclose($fp);
		return strlen($content);
	}
	$size=getimagesize($localurl);
	if(!$size[0]||!$size[2]){
		unlink($localurl);
		return false;
	}else return $size[0];
	return false;
}

function RP_baiduSpider($url){
	if(!function_exists('curl_init')){
		return false;
	}else{
		$ch = curl_init();
		$user_agent = "Mozilla/5.0 (compatible; Baiduspider/2.0; +http://www.baidu.com/search/spider.html)";   
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_HEADER,false);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch,CURLOPT_REFERER,'http://image.baidu.com/'); 
		curl_setopt($ch,CURLOPT_USERAGENT,$user_agent);
		$temp=curl_exec($ch);
		return $temp; 
	}
}

function RP_createImgAttach($url,$filename,$uid,$tid,$pid,$post_date){
	global $_G;
	$turl=$url;
	$turl=str_replace('jpeg','jpg',$turl);
	if(substr($turl,-4,1)=='.') $type=substr($turl,-4,4);
	else $type='.jpg';
	$data=array();
	$tableid=$tid%10;
	$data['filename']=$filename;
	dmkdir($_G['setting']['attachdir'].'/forum/'.date('Ym',$post_date).'/'.date('d',$post_date).'/');
	$data['attachment']=date('Ym',$post_date).'/'.date('d',$post_date).'/'.date('His',$post_date).strtolower(random(16)).$type;
	
	$localurl=$_G['setting']['attachdir']."/forum/".$data['attachment'];//路径
	$attachsaved=RP_downloadpic($url,$localurl);
	if(!$attachsaved) return false;
	$new_path='data/attachment/forum/'.$data['attachment'];
	$aid=C::t('forum_attachment')->insert(array('aid'=>NULL,'tid'=>$tid,'pid'=>$pid,'uid'=>$uid,'tableid'=>$tableid,'downloads'=>'0'),true);
	$data['aid']=$aid;
	$data['tid']=$tid;
	$data['pid']=$pid;
	$data['uid']=$uid;
	$data['dateline']=$post_date;
	$data['filesize']=filesize($new_path);
	$info=getimagesize($new_path);
	$data['width']=$info[0];
	$data['isimage']=1;
	DB::insert('forum_attachment_'.$tableid,$data);
	return $aid;
}	

function RP_checkattachdir($ym,$d) {
	global $_G;
	if(!is_dir($_G['setting']['attachdir']."/forum/".$ym."/")) dmkdir($_G['setting']['attachdir']."/forum/".$ym."/");
	if(!is_dir($_G['setting']['attachdir']."/forum/".$ym."/".$d."/")) dmkdir($_G['setting']['attachdir']."/forum/".$ym."/".$d."/");
}

function RP_checkPortalAttachDir($ym,$d) {
	global $_G;
	if(!is_dir($_G['setting']['attachdir']."/portal/".$ym."/")) dmkdir($_G['setting']['attachdir']."/portal/".$ym."/",0777);
	if(!is_dir($_G['setting']['attachdir']."/portal/".$ym."/".$d."/")) dmkdir($_G['setting']['attachdir']."/portal/".$ym."/".$d."/",0777);
}		



?>
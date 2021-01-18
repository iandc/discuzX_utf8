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

include_once DISCUZ_ROOT.'./source/plugin/nimba_romotepic/functions.php';

class plugin_nimba_romotepic {
	function  __construct() {
	    loadcache('plugin');
		global $_G;
		$vars = $_G['cache']['plugin']['nimba_romotepic'];
		$this->uids=explode(",",$this->vars['uids']);
		$this->open=intval($vars['open']);
		$this->selection=intval($vars['selection']);
		$this->minsize=intval($vars['minsize'])*1024;
		$this->siteurl=trim($vars['siteurl']);
		$this->forum=unserialize($vars['forum']);
		$this->group=unserialize($vars['group']);
		$this->timeDir=dgmdate(TIMESTAMP,'Ym/d/');
		$this->timeDirPath=dgmdate(TIMESTAMP,'Ym/d/His');
	}
	
	function downloadpic($src,$localurl){
	    if(!(file_exists($localurl)||copy($src,$localurl))){
			$content=dfsockopen($src);
			$this->pluginCache('src_content','content',strlen($content),0);
			if($content){
				$fp=fopen($localurl,"w");
				fwrite($fp,$content);
				fclose($fp);
			}
	    }
		if(file_exists($localurl)){
			if($content) return strlen($content);
			else return filesize($localurl);
		}
	    return false;
    }
	
	function fileext($filename) {
		return addslashes(strtolower(substr(strrchr($filename, '.'), 1, 10)));
	}	
	
	function pluginCache($cacheName,$varName,$data,$isarray){
		@require_once libfile('function/cache');
		if($isarray){
			$cacheArray .= "\$$varName=".arrayeval($data).";\n";
			writetocache($cacheName, $cacheArray);
		}else{
			$cacheArray .= "\$$varName=".$data.";\n";
			writetocache($cacheName, $cacheArray);
		}
	}	
}

class plugin_nimba_romotepic_forum extends plugin_nimba_romotepic{
	function loadForumCheckbox(){
		global $_G;
		$return = '';		
		if($_G['uid']&&$this->open&&in_array($_G['groupid'],$this->group)&&in_array($_G['fid'],$this->forum)){
		    include template('nimba_romotepic:post_newthread');
		}
		return $return;
	}
	
	function post_middle_output(){//新帖发布页,回复页
		return $this->loadForumCheckbox();
	}
	
	function forumdisplay_fastpost_btn_extra_output(){//列表页快速发布
		return $this->loadForumCheckbox();
	}

	function viewthread_fastpost_btn_extra(){//内容页快速回复
		return $this->loadForumCheckbox();
	}
	
    function post_getromotepic(){
	    global $_G;
		$down=intval($_GET['romotepic']);		
		$siteurl=$this->siteurl? $_G['siteurl']:$this->siteurl;
		if($down&&$this->open&&in_array($_G['groupid'],$this->group)&&in_array($_G['fid'],$this->forum)){
		   if(preg_match_all("/\[img[^\]]*\](.*)\[\/img\]/isU",$_GET['message'],$result)){//匹配[img]
				$this->pluginCache('src_result','result',$result,1);
			    foreach ($result[1] as $key=>$src){
				    $src=trim($src);
					if(substr($src,0,2)=='//') $src='http:'.$src;//强制使用http
				    if((stripos($src,$siteurl)==true)||(substr($src,0,7)!='http://'&&substr($src,0,8)!='https://')) continue;
					$ext=$this->fileext($src);
					if (!in_array($ext, array('jpg','jpeg','gif','png','bmp'))) {
						$ext = 'jpg';
					}
					require_once libfile('class/image');
					$romoteimage = new image;
					$this->checkattachdir();
					$localattachment=$this->timeDirPath.strtolower(random(16)).'.'.$ext;
					$localurl=$_G['setting']['attachdir']."/forum/".$localattachment;//相对路径
					$attachsavedsize=$this->downloadpic($src,$localurl);
					if($attachsavedsize){
						$watermarkstatus=unserialize($_G['setting']['watermarkstatus']);
						if($watermarkstatus['forum'] && empty($_G['forum']['disablewatermark'])) {
							$romoteimage->Watermark($localurl);
						}
						//组织参数	
						$pinfo=getimagesize($localurl);
						$width=$pinfo[0];
						$path_parts = pathinfo($src);
						$filename=$path_parts['filename'].'.'.$ext;//原始文件名
						$filesize=$attachsavedsize;
						$isimage=1;
						$remote=0;
						$thumb=0;
						//插入附件
						$aid=C::t('forum_attachment')->insert(array('aid'=>NULL,'tid'=>'0','pid'=>'0','uid'=>$_G['uid'],'tableid'=>'127','downloads'=>'0'),true);
						C::t('forum_attachment_unused')->insert(array('aid'=>$aid,'uid'=>$_G['uid'],'dateline'=>$_G['timestamp'],'filename'=>$filename,'filesize'=>$filesize,'attachment'=>$localattachment,'remote'=>$remote,'isimage'=>$isimage,'width'=>$width,'thumb'=>$thumb));
						$_GET['attachnew'][$aid]=array();
						$strfirst = strpos($_GET['message'],$result[0][$key]);
						if ($strfirst !== false){
							$_GET['message']=substr_replace($_GET['message'],'[attachimg]'.$aid.'[/attachimg]', $strfirst, strlen($result[0][$key]));
						}
					}				
			    }
		    }
		}
	}
	
	function checkattachdir() {
	    global $_G;
		if(!is_dir($_G['setting']['attachdir']."/forum/".$this->timeDir)) dmkdir($_G['setting']['attachdir']."/forum/".$this->timeDir);
	}
}
?>
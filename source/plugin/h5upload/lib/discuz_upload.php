<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: discuz_upload.php 34648 2014-06-18 02:53:07Z hypowang $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}


Class discuz_upload{

	var $attach = array();
	var $type = '';
	var $extid = 0;
	var $errorcode = 0;
	var $forcename = '';

	public function __construct() {
		global $_G;
		loadcache('plugin');
		$setconfig = $_G['cache']['plugin']['h5upload'];
		$this->setconfig = $setconfig;
	}

	function init($attach, $type = 'temp', $extid = 0, $forcename = '') {

		if(!is_array($attach) || empty($attach) || !$this->is_upload_file($attach['tmp_name']) || trim($attach['name']) == '' || $attach['size'] == 0) {
			$this->attach = array();
			$this->errorcode = -1;
			return false;
		} else {
			$this->type = $this->check_dir_type($type);
			$this->extid = intval($extid);
			$this->forcename = $forcename;

			$attach['size'] = intval($attach['size']);
			$attach['name'] =  trim($attach['name']);
			$attach['thumb'] = '';
			$attach['ext'] = $this->fileext($attach['name']);

			$attach['name'] =  dhtmlspecialchars($attach['name'], ENT_QUOTES);
			if(strlen($attach['name']) > 90) {
				$attach['name'] = cutstr($attach['name'], 80, '').'.'.$attach['ext'];
			}

			$attach['isimage'] = $this->is_image_ext($attach['ext']);
			$attach['extension'] = $this->get_target_extension($attach['ext']);
			$attach['attachdir'] = $this->get_target_dir($this->type, $extid);
			$attach['attachment'] = $attach['attachdir'].$this->get_target_filename($this->type, $this->extid, $this->forcename).'.'.$attach['extension'];
			$attach['target'] = getglobal('setting/attachdir').'./'.$this->type.'/'.$attach['attachment'];
			$this->attach = & $attach;
			$this->errorcode = 0;
			return true;
		}

	}

	function save($ignore = 0) {
		if($ignore) {
			if(!$this->save_to_local($this->attach['tmp_name'], $this->attach['target'])) {
				$this->errorcode = -103;
				return false;
			} else {
				$this->errorcode = 0;
				if($this->attach['isimage']){
					discuz_upload::rotateImage($this->attach['target']);
					if($this->setconfig['ban_muma'] && discuz_upload::checkHex($this->attach['target'])){
						discuz_upload::redrawImage($this->attach['target']);
					}
					if($this->setconfig['compress_open'] == 2){
						discuz_upload::compressImage($this->attach['target']);
					}
				}
				return true;
			}
		}

		if(empty($this->attach) || empty($this->attach['tmp_name']) || empty($this->attach['target'])) {
			$this->errorcode = -101;
		} elseif(in_array($this->type, array('group', 'album', 'category')) && !$this->attach['isimage']) {
			$this->errorcode = -102;
		} elseif(in_array($this->type, array('common')) && (!$this->attach['isimage'] && $this->attach['ext'] != 'ext')) {
			$this->errorcode = -102;
		} elseif(!$this->save_to_local($this->attach['tmp_name'], $this->attach['target'])) {
			$this->errorcode = -103;
		} elseif(($this->attach['isimage'] || $this->attach['ext'] == 'swf') && (!$this->attach['imageinfo'] = $this->get_image_info($this->attach['target'], true))) {
			$this->errorcode = -104;
			@unlink($this->attach['target']);
		} else {
			$this->errorcode = 0;
			if($this->attach['isimage']){
				discuz_upload::rotateImage($this->attach['target']);
				if($this->setconfig['ban_muma'] && discuz_upload::checkHex($this->attach['target'])){
					discuz_upload::redrawImage($this->attach['target']);
				}
				if($this->setconfig['compress_open'] == 2){
					discuz_upload::compressImage($this->attach['target']);
				}
			}
			return true;
		}

		return false;
	}

	function error() {
		return $this->errorcode;
	}

	function errormessage() {
		return lang('error', 'file_upload_error_'.$this->errorcode);
	}

	function fileext($filename) {
		return addslashes(strtolower(substr(strrchr($filename, '.'), 1, 10)));
	}

	function is_image_ext($ext) {
		static $imgext  = array('jpg', 'jpeg', 'gif', 'png', 'bmp');
		return in_array($ext, $imgext) ? 1 : 0;
	}

	function get_image_info($target, $allowswf = false) {
		$ext = discuz_upload::fileext($target);
		$isimage = discuz_upload::is_image_ext($ext);
		if(!$isimage && ($ext != 'swf' || !$allowswf)) {
			return false;
		} elseif(!is_readable($target)) {
			return false;
		} elseif($imageinfo = @getimagesize($target)) {
			list($width, $height, $type) = !empty($imageinfo) ? $imageinfo : array('', '', '');
			$size = $width * $height;
			if($ext == 'swf' && $type != 4 && $type != 13) {
				return false;
			} elseif($isimage && !in_array($type, array(1,2,3,6,13))) {
				return false;
			} elseif(!$allowswf && ($ext == 'swf' || $type == 4 || $type == 13)) {
				return false;
			}
			return $imageinfo;
		} else {
			return false;
		}
	}

	function is_upload_file($source) {
		return $source && ($source != 'none') && (is_uploaded_file($source) || is_uploaded_file(str_replace('\\\\', '\\', $source)));
	}

	function get_target_filename($type, $extid = 0, $forcename = '') {
		if($type == 'group' || ($type == 'common' && $forcename != '')) {
			$filename = $type.'_'.intval($extid).($forcename != '' ? "_$forcename" : '');
		} else {
			$filename = date('His').strtolower(random(16));
		}
		return $filename;
	}

	function get_target_extension($ext) {
		$safeext  = array('attach', 'jpg', 'jpeg', 'gif', 'png', 'swf', 'bmp', 'txt', 'zip', 'rar', 'mp3');
		if($this->setconfig['attach_extensions']){
			$this->setconfig['attach_extensions'] = str_replace(' ', '', $this->setconfig['attach_extensions']);
			$safeext  = array_merge($safeext, explode(",", $this->setconfig['attach_extensions']));
		}
		return strtolower(!in_array(strtolower($ext), $safeext) ? 'attach' : $ext);
	}

	function get_target_dir($type, $extid = '', $check_exists = true) {

		$subdir = $subdir1 = $subdir2 = '';
		if($type == 'album' || $type == 'forum' || $type == 'portal' || $type == 'category' || $type == 'profile') {
			$subdir1 = date('Ym');
			$subdir2 = date('d');
			$subdir = $subdir1.'/'.$subdir2.'/';
		} elseif($type == 'group' || $type == 'common') {
			$subdir = $subdir1 = substr(md5($extid), 0, 2).'/';
		}

		$check_exists && discuz_upload::check_dir_exists($type, $subdir1, $subdir2);

		return $subdir;
	}

	function check_dir_type($type) {
		return !in_array($type, array('forum', 'group', 'album', 'portal', 'common', 'temp', 'category', 'profile')) ? 'temp' : $type;
	}

	function check_dir_exists($type = '', $sub1 = '', $sub2 = '') {

		$type = discuz_upload::check_dir_type($type);

		$basedir = !getglobal('setting/attachdir') ? (DISCUZ_ROOT.'./data/attachment') : getglobal('setting/attachdir');

		$typedir = $type ? ($basedir.'/'.$type) : '';
		$subdir1  = $type && $sub1 !== '' ?  ($typedir.'/'.$sub1) : '';
		$subdir2  = $sub1 && $sub2 !== '' ?  ($subdir1.'/'.$sub2) : '';

		$res = $subdir2 ? is_dir($subdir2) : ($subdir1 ? is_dir($subdir1) : is_dir($typedir));
		if(!$res) {
			$res = $typedir && discuz_upload::make_dir($typedir);
			$res && $subdir1 && ($res = discuz_upload::make_dir($subdir1));
			$res && $subdir1 && $subdir2 && ($res = discuz_upload::make_dir($subdir2));
		}

		return $res;
	}

	function save_to_local($source, $target) {
		if(!discuz_upload::is_upload_file($source)) {
			$succeed = false;
		}elseif(function_exists('move_uploaded_file') && @move_uploaded_file($source, $target)) {
			$succeed = true;
		}elseif(@copy($source, $target)) {
			$succeed = true;
		}elseif (@is_readable($source) && (@$fp_s = fopen($source, 'rb')) && (@$fp_t = fopen($target, 'wb'))) {
			while (!feof($fp_s)) {
				$s = @fread($fp_s, 1024 * 512);
				@fwrite($fp_t, $s);
			}
			fclose($fp_s); fclose($fp_t);
			$succeed = true;
		}
		if($succeed)  {
			$this->errorcode = 0;
			@chmod($target, 0644); @unlink($source);
		} else {
			$this->errorcode = 0;
		}

		return $succeed;
	}

	function make_dir($dir, $index = true) {
		$res = true;
		if(!is_dir($dir)) {
			$res = @mkdir($dir, 0777);
			$index && @touch($dir.'/index.html');
		}
		return $res;
	}

	//检测图片是否含木马
	function checkHex($image) {
		$resource = fopen($image, 'rb');
		$fileSize = filesize($image);
		fseek($resource, 0);
		if ($fileSize > 512) {
			$hexCode = bin2hex(fread($resource, 512));
			fseek($resource, $fileSize - 512);
			$hexCode .= bin2hex(fread($resource, 512));
		} else {
			$hexCode = bin2hex(fread($resource, $fileSize));
		}
		fclose($resource);
		if (preg_match("/(3c25.*?28.*?29.*?253e)|(3c3f.*?28.*?29.*?3f3e)|(3C534352495054)|(2F5343524950543E)|(3C736372697074)|(2F7363726970743E)/is", $hexCode)){
			return true;
		}else{
			return false;
		}
	}

	//对图片进行重绘操作，防止图片木马
	function redrawImage($source) {
		$image = new image();
		$return = $image->init('thumb', $source, '', 1);
		if($return <= 0) {
			return false;
		}
		$image->param['thumbwidth'] = $image->imginfo['width'];
		$image->param['thumbheight'] = $image->imginfo['height'];
		$image->param['thumbtype'] = 1;
		if(!$image->libmethod){
			$image->Thumb_GD();
		}else{
			$image->Thumb_IM();
		}
	}

	//对图片进行矫正旋转
	function rotateImage($source) {
		if(function_exists("exif_read_data")){ 
			$exifInfo = exif_read_data($source, 'EXIF', 0);//获取图片的exif信息
			if ($exifInfo && $exifInfo['Orientation']){
				$image = new image();
				$return = $image->init('thumb', $source, '', 1);
				if($return <= 0) {
					return false;
				}
				if(!$image->libmethod){
					$imagefunc = $image->imagefunc;
					$attach_photo = $image->loadsource();
					if($attach_photo < 0) {
						return false;
					}
					switch ($exifInfo['Orientation']) {
						case 8:
							$attach_photo = imagerotate($attach_photo, 90, 0);
							break;
						case 3:
							$attach_photo = imagerotate($attach_photo, 180, 0);
							break;
						case 6:
							$attach_photo = imagerotate($attach_photo, -90, 0);
							break;
					}
					if($image->imginfo['mime'] == 'image/jpeg') {
						@$imagefunc($attach_photo, $source, $image->param['thumbquality']);
					} else {
						@$imagefunc($attach_photo, $source);
					}
				}else{
					$im = new Imagick();
					$im->readImage(realpath($source));
					switch ($exifInfo['Orientation']) {
						case 8:
							$im->rotateimage('#fff', 90);
							break;
						case 3:
							$im->rotateimage('#fff', 180);
							break;
						case 6:
							$im->rotateimage('#fff', -90);
							break;
					}
					$im->setImageCompressionQuality($image->param['thumbquality']);
					if(!$im->writeImage($source)) {
						$im->destroy();
						return false;
					}
					$im->destroy();
				}
			}
			return false;
		}
		return false;
	}

	//对图片进行压缩
	function compressImage($source) {
		$fileSize = filesize($source);
		if($fileSize >= $this->setconfig['compress_size']){
			$image = new image();
			$image->param['thumbquality'] = $this->setconfig['compress_quality'];
			$image->Thumb($source, '', $this->setconfig['compress_width'], $this->setconfig['compress_height'], 1, 1);
		}
	}
}

?>

jQuery.noConflict();
	
var WebUpload;
var sdCloseTime = 2;
var DOWNREMOTESTATUS = 0;
var IMAGEUPLOADING = 0;

if (WebUpload == undefined) {
	WebUpload = function(settings) {
		this.initWebUpload(settings);
	};
}

var getBasePath = function() {
	var els = document.getElementsByTagName('script'), src;
	for (var i = 0, len = els.length; i < len; i++) {
		src = els[i].src || '';
		if (/webuploader[\w\-\.]*\.js/.test(src)) {
			return src.substring(0, src.lastIndexOf('/') + 1);
		}
	}
	return '';
};

WebUpload.prototype.initWebUpload = function(userSettings) {
	try {
		this.customSettings = {};
		this.settings = {};
		this.initSettings(userSettings);
	} catch (ex) {
		throw ex;
	}
};

WebUpload.prototype.initSettings = function (userSettings) {

	this.ensureDefault = function(settingName, defaultValue) {
		var setting = userSettings[settingName];
		if (setting != undefined) {
			this.settings[settingName] = setting;
		} else {
			this.settings[settingName] = defaultValue;
		}
	};
	this.ensureDefault("auto", false);
	this.ensureDefault("compress", false);
	this.ensureDefault("upload_url", "");
	this.ensureDefault("pick", undefined);
	this.ensureDefault("dnd", undefined);
	this.ensureDefault("paste", undefined);
	this.ensureDefault("accept", null);
	this.ensureDefault("fileVal", "file");
	this.ensureDefault("chunked", false);
	this.ensureDefault("chunkSize", 5242880);
	this.ensureDefault("post_params", {});
	this.ensureDefault("threads", 1);
	this.ensureDefault("fileNumLimit", undefined);
	this.ensureDefault("fileSingleSizeLimit", undefined);

	this.ensureDefault("custom_settings", {});
	this.customSettings = this.settings.custom_settings;

	if((this.customSettings.uploadSource == 'portal' && (this.customSettings.uploadType == 'portal' || this.customSettings.uploadType == 'blog')) || (this.customSettings.uploadSource == 'forum' && this.customSettings.uploadType == 'image')) {
		if(this.settings.dnd != undefined) {
			if(this.customSettings.dndStyle == 1){
				$('imgattachlist').parentNode.className += ' uploaderdndbox';
				var imguploadtip = document.createElement('span');
				imguploadtip.innerHTML = '或将图片直接拖至下框';
				imguploadtip.className = 'spanuploaderdndtip';
				$('imgSpanButtonPlaceholder').parentNode.appendChild(imguploadtip);
			}else if(this.customSettings.dndStyle == 2){
				$('imgattachlist').parentNode.className += ' uploaderdndbox';
				var imageuploaderdndtip = document.createElement('div');
				imageuploaderdndtip.id = 'imageuploaderdndtip';
				imageuploaderdndtip.className = 'uploaderdndtip';
				imageuploaderdndtip.innerHTML = '或将图片直接拖到这里';
				$('imgattachlist').parentNode.appendChild(imageuploaderdndtip);
				if($('imgattachlist').parentNode.getElementsByTagName("td").length > 0) {
					$('imageuploaderdndtip').style.display = 'none';
				}
			}else if(this.customSettings.dndStyle == 3){
				var imageuploaderdndtip = document.createElement('div');
				imageuploaderdndtip.id = 'imageuploaderdndboxs';
				imageuploaderdndtip.className = 'uploaderdndboxs';
				imageuploaderdndtip.innerHTML = '或将图片直接拖到这里';
				$('imgSpanButtonPlaceholder').parentNode.appendChild(imageuploaderdndtip);
				this.settings.dnd = '#imageuploaderdndboxs';
			}
		}
		if(this.customSettings.uploadSource == 'forum' && this.customSettings.uploadType == 'image' && this.customSettings.insertAllImage == '1') {
			var image_tblheader = document.createElement('div');
			image_tblheader.id = 'image_tblheader';
			image_tblheader.className = 'mtn bbs pbn cl';
			image_tblheader.innerHTML = '<span class="z"><a onclick="insertAllImageTag(0);return false;" href="javascript:;" class="xi2">插入全部图片</a>（<a onclick="insertAllImageTag(1);return false;" href="javascript:;" class="xi2">逆序</a>）</span><span class="y"><a onclick="deleteAllImageTag();return false;" href="javascript:;" class="xi2">删除全部图片</a></span>';  
			image_tblheader.style.display = 'none';  
			var imgboxdiv = $('imgSpanButtonPlaceholder').parentNode.parentNode;
			imgboxdiv.insertBefore(image_tblheader,imgboxdiv.children[1]);
			if($('imgattachlist').parentNode.getElementsByTagName("td").length > 0) {
				$('image_tblheader').style.display = '';
			}
		}
		if(this.customSettings.uploadSource == 'portal' && (this.customSettings.uploadType == 'portal' || this.customSettings.uploadType == 'blog') && this.customSettings.insertAllImage == '1') {
			var image_tblheader = document.createElement('div');
			image_tblheader.id = 'image_tblheader';
			image_tblheader.className = 'mtn bbs pbn cl';
			image_tblheader.innerHTML = '<span class="z"><a onclick="insertAllImagePortal(0);return false;" href="javascript:;" class="xi2">插入全部图片</a>（<a onclick="insertAllImagePortal(1);return false;" href="javascript:;" class="xi2">逆序</a>）</span>';  
			if(this.customSettings.uploadType == 'portal'){
				image_tblheader.innerHTML += '<span class="y"><a onclick="deleteAllImagePortal();return false;" href="javascript:;" class="xi2">删除全部图片</a></span>';  
			}
			image_tblheader.style.display = 'none';  
			var imgboxdiv = $('imgSpanButtonPlaceholder').parentNode.parentNode;
			imgboxdiv.insertBefore(image_tblheader,imgboxdiv.children[1]);
			if($('imgattachlist').parentNode.getElementsByTagName("td").length > 0) {
				$('image_tblheader').style.display = '';
			}
		}
	}else if((this.customSettings.uploadSource == 'portal' || this.customSettings.uploadSource == 'forum') && this.customSettings.uploadType == 'attach') {
		if(this.settings.dnd != undefined) {
			if(this.customSettings.dndStyle == 1){
				$('attachlist').parentNode.className += ' uploaderdndbox';
				var attachuploadtip = document.createElement('span');
				attachuploadtip.innerHTML = '或将文件直接拖至下框';
				attachuploadtip.className = 'spanuploaderdndtip';
				$('spanButtonPlaceholder').parentNode.appendChild(attachuploadtip);
			}else if(this.customSettings.dndStyle == 2){
				$('attachlist').parentNode.className += ' uploaderdndbox';
				var attachuploaderdndtip = document.createElement('div');
				attachuploaderdndtip.id = 'attachuploaderdndtip';
				attachuploaderdndtip.className = 'uploaderdndtip';
				attachuploaderdndtip.innerHTML = '或将文件直接拖到这里';
				$('attachlist').parentNode.appendChild(attachuploaderdndtip);
				if($('attachlist').parentNode.getElementsByTagName("td").length > 0) {
					$('attachuploaderdndtip').style.display = 'none';
				}
			}else if(this.customSettings.dndStyle == 3){
				var attachuploaderdndtip = document.createElement('div');
				attachuploaderdndtip.id = 'attachuploaderdndboxs';
				attachuploaderdndtip.className = 'uploaderdndboxs';
				attachuploaderdndtip.innerHTML = '或将文件直接拖到这里';
				$('spanButtonPlaceholder').parentNode.appendChild(attachuploaderdndtip);
				this.settings.dnd = '#attachuploaderdndboxs';
			}
		}
	}

	if(this.customSettings.uploadStorage){
		this.settings.chunked = false;
	}
	var self = this;

	WebUploader.Uploader.register({
		"before-send" : "beforeSend",
	},{
		beforeSend : function(block) {
			var uploader = this.owner; 
			var deferred = new WebUploader.Deferred(); 
			if(self.customSettings.uploadStorage == 'cl_qiniuyun'){

				deferred.resolve();  
			}else if(self.customSettings.uploadStorage == 'cl_aliyun'){

				deferred.resolve();  
			}else if(self.customSettings.uploadStorage == 'zhanmishu_storage'){

				deferred.resolve();  
			}else{
				if(block.chunks>1){
					if (in_array(block.chunk.toString(), block.file.block_info) && block.chunks > block.chunk + 1) { 
						deferred.reject();
					} else {   
						deferred.resolve();  
					}
					self.addPostParam('md5value', block.file.wholeMd5);
					uploader.options.server = self.settings.upload_url.replace('misc.php?mod=swfupload', 'plugin.php?id=h5upload:upload');
				}else{
					uploader.options.server = self.settings.upload_url;
					deferred.resolve();
				}
			}
			return deferred.promise();
		}
	});

	var uploader = WebUploader.create({
		auto: false,
		swf: getBasePath() + 'Uploader.swf',
		compress: this.settings.compress,
		server: this.settings.upload_url,
		pick: this.settings.pick,
		dnd: this.settings.dnd,
		paste: this.settings.paste,
		accept: this.settings.accept,
		fileVal: this.settings.fileVal,
		chunked: this.settings.chunked,
		chunkSize: this.settings.chunkSize,
		formData: this.settings.post_params,
		threads: this.settings.threads,
		fileNumLimit: this.settings.fileNumLimit,
		fileSingleSizeLimit: this.settings.fileSingleSizeLimit,
		duplicate: true,
		disableWidgets: 'log'
	});

	this.uploader = uploader;

	uploader.on('beforeFileQueued', function(file) {
		fileDialogStart.apply(self);
	});

	uploader.on('fileQueued', function(file) {
		fileQueued.apply(self, [file]);
	});

	uploader.on('startUpload', function() {
		fileDialogComplete.apply(self);
	});

	uploader.on('uploadStart', function(file) {
		uploadStart.apply(self, [file]);
	});

	uploader.on('uploadProgress', function(file, percentage) {
		uploadProgress.apply(self, [file, percentage]);
	});

	uploader.on('uploadError', function(file, reason) {
		uploadError.apply(self, [file, reason]);
	});

	uploader.on('uploadSuccess', function(file, response) {
		var serverData = response;
		if(self.customSettings.uploadStorage == 'cl_qiniuyun'){
            for(key in uploader.options.formData){
                serverData[key]  = uploader.options.formData[key];
            }
			serverData['name'] = serverData['name'].replace(/\(/g, '[').replace(/\)/g, ']').replace(/CONTENT-TRANSFER-ENCODING/g, escape('CONTENTTRANSFERENCODING'));
			serverData['_raw'] = '';
			//serverData['imageinfo'] = '';
            //console.log(serverData);
            var sendString = serializeData(serverData);
            var x = new Ajax('JSON');
            x.post(self.settings.upload_url.replace('misc.php?mod=swfupload', 'plugin.php?id=cl_qiniuyun:upload&inajax=1'), sendString, function(serverData){

				uploadSuccess.apply(self, [file, serverData]);
			});
		}else if(self.customSettings.uploadStorage == 'cl_aliyun'){

		}else if(self.customSettings.uploadStorage == 'zhanmishu_storage'){
            var sendString = serializeData(uploader.options.formData);
            var x = new Ajax('JSON');
			x.post('plugin.php?id=zhanmishu_storage:upload&current=forum&operation=upload', sendString, function(serverData){
				uploadSuccess.apply(self, [file, serverData]);
			})
		}else{
			uploadSuccess.apply(self, [file, serverData]);
		}
	});

	uploader.on('error', function(code) {
		fileQueueError.apply(self, [code]);
	});
	uploader.on('uploadComplete', function( file ) {
		uploadComplete.apply(self, [file]);
	});
};

WebUpload.prototype.setUploadURL = function (url) {
	this.uploader.options.server = url.toString();
};

WebUpload.prototype.addPostParam = function (name, value) {
	this.uploader.options.formData[name] = value;
};

function fileDialogStart() {
	if(this.customSettings.uploadSource == 'forum') {
		this.customSettings.alertType = 0;
		if(this.customSettings.uploadFrom == 'fastpost') {
			if(typeof forum_post_inited == 'undefined') {
				appendscript(JSPATH + 'forum_post.js?' + VERHASH);
			}
		}
	}
}

function fileQueued(file) {
	try {
		var self = this;
		var createQueue = true;
		if (this.customSettings.uploadSource == 'forum' && this.customSettings.uploadType == 'poll') {
			var inputObj = $(this.customSettings.progressTarget + '_aid');
			if (inputObj && parseInt(inputObj.value)) {
				this.addPostParam('aid', inputObj.value);
			}
		} else if (this.customSettings.uploadSource == 'portal') {
			var inputObj = $('catid');
			if (inputObj && parseInt(inputObj.value)) {
				this.addPostParam('catid', inputObj.value);
			}else if(typeof check_catid == 'function' && !check_catid()) {
				this.uploader.cancelFile(file);
				return false;
			}
		}
		if(this.settings.dnd != undefined) {
			if((this.customSettings.uploadSource == 'portal' && (this.customSettings.uploadType == 'portal' || this.customSettings.uploadType == 'blog')) || (this.customSettings.uploadSource == 'forum' && this.customSettings.uploadType == 'image')) {
				if($('imageuploaderdndtip')) {
					$('imageuploaderdndtip').style.display = 'none';
				}
			}else if((this.customSettings.uploadSource == 'portal' || this.customSettings.uploadSource == 'forum') && this.customSettings.uploadType == 'attach') {
				if($('attachuploaderdndtip')) {
					$('attachuploaderdndtip').style.display = 'none';
				}
			}
		}
		var progress = new FileProgress(file, this.customSettings.progressTarget);
		if (this.customSettings.uploadSource == 'forum') {
			if (this.customSettings.maxAttachNum != undefined) {
				if (this.customSettings.maxAttachNum > 0) {
					this.customSettings.maxAttachNum--;
				} else {
					this.customSettings.alertType = 6;
					createQueue = false;
				}
			}

			if (createQueue && this.customSettings.maxSizePerDay != undefined) {
				if (this.customSettings.maxSizePerDay - file.size > 0) {
					this.customSettings.maxSizePerDay = this.customSettings.maxSizePerDay - file.size
				} else {
					this.customSettings.alertType = 11;
					createQueue = false;
				}
			}
			if (createQueue && this.customSettings.filterType != undefined) {
				var fileSize = this.customSettings.filterType[file.type.substr(1).toLowerCase()];
				if (fileSize != undefined && fileSize && file.size > fileSize) {
					this.customSettings.alertType = 5;
					createQueue = false;
				}
			}

		}
		if (createQueue) {
			if(this.customSettings.uploadStorage == 'cl_qiniuyun'){
				progress.setStatus("等待上传...");
				var filename = file.name.replace(/\(/g, '[').replace(/\)/g, ']').replace(/CONTENT-TRANSFER-ENCODING/g, escape('CONTENTTRANSFERENCODING'));
				var x = new Ajax();
				x.post(this.settings.upload_url.replace('misc.php?mod=swfupload', 'plugin.php?id=cl_qiniuyun:token&inajax=1'), 'filename='+filename, function(res){
					file.token = res;
					self.uploader.options.server = 'http://up.qiniu.com/';
					self.uploader.upload(file);
				})
			}else if(this.customSettings.uploadStorage == 'cl_aliyun'){

			}else if(this.customSettings.uploadStorage == 'zhanmishu_storage'){
				var x = new Ajax('JSON');
				x.get('plugin.php?id=zhanmishu_storage:token',function(res){
                    if(res.apiselect){
						if (res.apiselect == '1') {
							self.uploader.options.server = res.host;
							self.uploader.options.fileVal = 'file';
							self.addPostParam('signature', res.signature);
							self.addPostParam('OSSAccessKeyId', res.OSSAccessKeyId);
							self.addPostParam('success_action_status', '200');
							self.addPostParam('callback', '');
							self.addPostParam('filename', file.name);
							self.addPostParam('filesize', file.size);
							self.addPostParam('Content-Disposition', file.size);
							self.addPostParam('policy', res.policy);
							self.addPostParam('key', res.key);
						}else if (res.apiselect == '2') {
							self.uploader.options.server = res.host;
							self.uploader.options.fileVal = 'file';
							self.addPostParam('bucket', res.bucket);
							self.addPostParam('expiration', res.expiration);
							self.addPostParam('policy', res.policy);
							self.addPostParam('authorization', res.authorization);
							self.addPostParam('filename', file.name);
							self.addPostParam('filesize', file.size);
							self.addPostParam('key', res.key);
						}
						self.uploader.upload(file);
					}
				})
			}else{
				if(this.uploader.options.chunked && file.size > this.uploader.options.chunkSize){
					this.uploader.md5File(file).progress(function(percentage) {
						progress.setStatus("文件读取("+Math.round(percentage * 100) + "%)...");
						progress.toggleCancel(false, self.uploader);
					}).then(function (fileMd5) {
						file.wholeMd5 = fileMd5;
						progress.setStatus("等待上传...");
						progress.toggleCancel(true, self.uploader);
						//var x = new Ajax();
						//x.getJSON(self.settings.upload_url.replace('misc.php?mod=swfupload', 'plugin.php?id=h5upload:upload&chunkop=checkchunks&inajax=1') + '&uid=' + self.uploader.options.formData.uid +'&hash=' + self.uploader.options.formData.hash +'&md5value=' + file.wholeMd5 +'&chunksize=' + self.uploader.options.chunkSize, function(s) {
							//console.log(s);
							//var block_info = [];
							//if(s !== null) {
								//var res = s.data;
								//for (var i in res) {
									//block_info.push(res[i]);
								//}
							//}
							//file.block_info = block_info;
							//self.uploader.upload(file);
						//});
						jQuery.ajax({
							type: "POST",
							url: self.settings.upload_url.replace('misc.php?mod=swfupload', 'plugin.php?id=h5upload:upload&chunkop=checkchunks&inajax=1'),
							data: {
								uid: self.uploader.options.formData.uid,
								hash: self.uploader.options.formData.hash,
								md5value: file.wholeMd5,
								chunksize: self.uploader.options.chunkSize
							},
							cache: false,
							async: false,
							success: function(res) {
								var block_info = [];
								console.log(res);
								if (res) {
									res = res.data;
									for (var i in res) {
										block_info.push(res[i]);
									}
								}
								file.block_info = block_info;
							}
						});
						self.uploader.upload(file);
					});
				}else{
					progress.setStatus("等待上传...");
					this.uploader.upload(file);
				}
			}
		} else {
			this.uploader.cancelFile(file);
			progress.setStatus(this.customSettings.alertType ? STATUSMSG[this.customSettings.alertType] : "Cancelled");
			progress.setCancelled();
		}
		progress.toggleCancel(true, this.uploader);

	} catch(ex) {
		showDialog(ex, 'notice', null, null, 0, null, null, null, null, sdCloseTime);
	}
}

function fileQueueError(errorCode) {
	try {
		var err = '';
		switch (errorCode) {
		case 'F_EXCEED_SIZE':
			err = '单个文件大小不得超过' + WebUploader.Base.formatSize(this.uploader.option('fileSingleSizeLimit')) + '！';
			break;
		case 'Q_EXCEED_NUM_LIMIT':
			err = '最多只能上传' + this.settings.fileNumLimit + '个！';
			break;
		case 'Q_EXCEED_SIZE_LIMIT':
			err = '上传文件总大小超出' + WebUploader.Base.formatSize(this.uploader.option('fileSizeLimit')) + '！';
			break;
		case 'Q_TYPE_DENIED':
			err = '无效文件类型，请上传正确的文件格式！';
			break;
		case 'F_DUPLICATE':
			err = '请不要重复上传相同文件！';
			break;
		default:
			err = '上传错误，请刷新重试！' + code;
			break;
		}
		showDialog(err, 'notice', null, null, 0, null, null, null, null, sdCloseTime);
	} catch (ex) {
		showDialog(ex, 'notice', null, null, 0, null, null, null, null, sdCloseTime);
    }
}

function fileDialogComplete() {
	try {
		if(this.customSettings.uploadSource == 'forum') {
			if(this.customSettings.uploadType == 'attach') {
				if(typeof switchAttachbutton == "function") {
					switchAttachbutton('attachlist');
				}
				try {
					$('attach_tblheader').style.display = '';
					$('attach_notice').style.display = '';
				} catch (ex) {}
			} else if(this.customSettings.uploadType == 'image') {
				if(typeof switchImagebutton == "function") {
					//switchImagebutton('imgattachlist');
					switchButton('imgattachlist', 'image');
					$(editorid + '_image_menu').style.height = '';
				}
				try {
					if ($('image_tblheader')) {
						$('image_tblheader').style.display = '';
					}
					$('imgattach_notice').style.display = '';
				} catch (ex) {}
			}
			var objId = this.customSettings.uploadType == 'attach' ? 'attachlist' : 'imgattachlist';
			var listObj = $(objId);
			var tableObj = listObj.getElementsByTagName("table");
			if(!tableObj.length) {
				listObj.innerHTML = "";
			}
		} else if(this.customSettings.uploadType == 'blog') {
			if(typeof switchImagebutton == "function") {
				switchImagebutton('imgattachlist');
			}
		}
	} catch (ex)  {
		showDialog(ex, 'notice', null, null, 0, null, null, null, null, sdCloseTime);
	}
}

function uploadStart(file) {
	try {
		IMAGEUPLOADING = 1;
		if (this.customSettings.uploadSource == 'forum' && this.customSettings.uploadType == 'poll') {
			var preObj = $(this.customSettings.progressTarget);
			preObj.style.display = 'none';
			preObj.innerHTML = '';
		}
		if(this.customSettings.uploadStorage == 'cl_qiniuyun'){
			this.uploader.options.fileVal = 'file';
			this.addPostParam('token', file.token);
		}else if(this.customSettings.uploadStorage == 'cl_aliyun'){

		}
		var progress = new FileProgress(file, this.customSettings.progressTarget);
		progress.setStatus("上传中...");
		progress.toggleCancel(true, this.uploader);
		if (this.customSettings.uploadSource == 'forum') {
			var objId = this.customSettings.uploadType == 'attach' ? 'attachlist': 'imgattachlist';
			var attachlistObj = $(objId).parentNode;
			attachlistObj.scrollTop = $(file.id).offsetTop - attachlistObj.clientHeight;
		}
	} catch(ex) {
		showDialog(ex, 'notice', null, null, 0, null, null, null, null, sdCloseTime);
	}
}

function uploadProgress(file, percentage) {
	try {
		var progress = new FileProgress(file, this.customSettings.progressTarget);
		progress.setStatus("正在上传(" + Math.round(percentage * 100) + "%)...");
		progress.toggleCancel(true, this.uploader);
	} catch (ex) {
		showDialog(ex, 'notice', null, null, 0, null, null, null, null, sdCloseTime);
	}
}

function uploadSuccess(file, serverData) {
	try {
		var progress = new FileProgress(file, this.customSettings.progressTarget);
		if (this.customSettings.uploadSource == 'forum') {
			if (this.customSettings.uploadType == 'poll') {
				//var data = eval('(' + serverData + ')');
				var data = serverData;
				if (parseInt(data.aid)) {
					var preObj = $(this.customSettings.progressTarget);
					preObj.innerHTML = "";
					preObj.style.display = '';
					var img = new Image();
					img.src = IMGDIR + '/attachimg_2.png'; //data.smallimg;
					var imgObj = document.createElement("img");
					imgObj.src = img.src;
					imgObj.className = "cur1";
					imgObj.onmouseout = function() {
						hideMenu('poll_img_preview_' + data.aid + '_menu');
					}; //"hideMenu('poll_img_preview_"+data.aid+"_menu');";
					imgObj.onmouseover = function() {
						showMenu({
							'menuid': 'poll_img_preview_' + data.aid + '_menu',
							'ctrlclass': 'a',
							'duration': 2,
							'timeout': 0,
							'pos': '34'
						});
					}; //"showMenu({'menuid':'poll_img_preview_"+data.aid+"_menu','ctrlclass':'a','duration':2,'timeout':0,'pos':'34'});";
					preObj.appendChild(imgObj);
					var inputObj = document.createElement("input");
					inputObj.type = 'hidden';
					inputObj.name = 'pollimage[]';
					inputObj.id = this.customSettings.progressTarget + '_aid';
					inputObj.value = data.aid;
					preObj.appendChild(inputObj);
					var preImgObj = document.createElement("span");
					preImgObj.style.display = 'none';
					preImgObj.id = 'poll_img_preview_' + data.aid + '_menu';
					img = new Image();
					img.src = data.smallimg;
					imgObj = document.createElement("img");
					imgObj.src = img.src;
					preImgObj.appendChild(imgObj);
					preObj.appendChild(preImgObj);
				}
			} else if (this.customSettings.uploadType == 'sort') {
				var str = serverData['_raw'];
				if(str == '') return;
				var arr = str.split('|');
				if(arr[0] == 'DISCUZUPLOAD' && arr[2] == 0) {
					this.customSettings.recall(arr[3], arr[5], arr[6]);
				} else {
					var sizelimit = '';
					if(arr[7] == 'ban') {
						sizelimit = '(附件类型被禁止)';
					} else if(arr[7] == 'perday') {
						sizelimit = '(不能超过 ' + arr[8] + ' 字节)';
					} else if(arr[7] > 0) {
						sizelimit = '(不能超过 ' + arr[7] + ' 字节)';
					}
					showError(STATUSMSG[arr[2]] + sizelimit);
				}
				if($('attachlimitnotice')) {
					ajaxget('forum.php?mod=ajax&action=updateattachlimit&fid=' + fid, 'attachlimitnotice');
				}
			} else {
				aid = parseInt(serverData);
				if (aid > 0) {
					if (this.customSettings.uploadType == 'attach') {
						ajaxget('forum.php?mod=ajax&action=attachlist&aids=' + aid + (!fid ? '': '&fid=' + fid) + (typeof resulttype == 'undefined' ? '': '&result=simple'), file.id, null, '', null, function(){
							if(file.source.paste){
								insertAttachimgTag(aid);doane(event);
							}
						});
					} else if (this.customSettings.uploadType == 'image') {
						var tdObj = getInsertTdId(this.customSettings.imgBoxObj, 'image_td_' + aid);
						ajaxget('forum.php?mod=ajax&action=imagelist&type=single&pid=' + pid + '&aids=' + aid + (!fid ? '': '&fid=' + fid), tdObj.id, null, '', null, function(){
							if(file.source.paste){
								insertAttachimgTag(aid);doane(event);
							}
						});
						$(file.id).style.display = 'none';
					}
				} else {
					aid = aid < -1 ? Math.abs(aid) : aid;
					if (typeof STATUSMSG[aid] == "string") {
						progress.setStatus(STATUSMSG[aid]);
						showDialog(STATUSMSG[aid], 'notice', null, null, 0, null, null, null, null, sdCloseTime);
					} else {
						progress.setStatus("取消上传");
					}
					this.uploader.cancelFile(file);
					progress.setCancelled();
					progress.toggleCancel(true, this.uploader);
					//var stats = this.getStats();
					//var obj = {'successful_uploads':--stats.successful_uploads, 'upload_cancelled':++stats.upload_cancelled};
					//this.setStats(obj);
				}
			}
		} else if (this.customSettings.uploadType == 'album') {
			//var data = eval('(' + serverData + ')');
			var data = serverData;
			if (parseInt(data.picid)) {
				var newTr = document.createElement("TR");
				var newTd = document.createElement("TD");
				var img = new Image();
				img.src = data.url;
				var imgObj = document.createElement("img");
				imgObj.src = img.src;
				newTd.className = 'c';
				newTd.appendChild(imgObj);
				newTr.appendChild(newTd);
				newTd = document.createElement("TD");
				newTd.innerHTML = '<strong>' + file.name + '</strong>';
				newTr.appendChild(newTd);
				newTd = document.createElement("TD");
				newTd.className = 'd';
				newTd.innerHTML = '图片描述<br/><textarea name="title[' + data.picid + ']" cols="40" rows="2" class="pt"></textarea>';
				newTr.appendChild(newTd);
				this.customSettings.imgBoxObj.appendChild(newTr);
			} else {
				showDialog('图片上传失败', 'notice', null, null, 0, null, null, null, null, sdCloseTime);
			}
			$(file.id).style.display = 'none';
		} else if (this.customSettings.uploadType == 'blog') {
			//var data = eval('(' + serverData + ')');
			var data = serverData;
			if (parseInt(data.picid)) {
				var tdObj = getInsertTdId(this.customSettings.imgBoxObj, 'image_td_' + data.picid);
				var img = new Image();
				img.src = data.url;
				var imgObj = document.createElement("img");
				imgObj.src = img.src;
				imgObj.className = "cur1";
				imgObj.onclick = function() {
					insertImage(data.bigimg);
				};
				tdObj.appendChild(imgObj);
				var inputObj = document.createElement("input");
				inputObj.type = 'hidden';
				inputObj.name = 'picids[' + data.picid + ']';
				inputObj.value = data.picid;
				tdObj.appendChild(inputObj);
				if(file.source.paste){
					imgObj.onclick();
				}
				if ($('image_tblheader')) {
					$('image_tblheader').style.display = '';
				}
			} else {
				showDialog('图片上传失败', 'notice', null, null, 0, null, null, null, null, sdCloseTime);
			}
			$(file.id).style.display = 'none';
		} else if (this.customSettings.uploadSource == 'portal') {
			//var data = eval('(' + serverData + ')');
			var data = serverData;
			if (data.aid) {
				if (this.customSettings.uploadType == 'attach') {
					ajaxget('portal.php?mod=attachment&op=getattach&type=attach&id=' + data.aid, file.id);
					if ($('attach_tblheader')) {
						$('attach_tblheader').style.display = '';
					}
				} else {
					var tdObj = getInsertTdId(this.customSettings.imgBoxObj, 'attach_list_' + data.aid);
					ajaxget('portal.php?mod=attachment&op=getattach&id=' + data.aid, tdObj.id, null, '', null, function(){
						if(file.source.paste){
							$('attach_list_'+data.aid).getElementsByTagName('img')[0].onclick();
						}
					});
					if ($('image_tblheader')) {
						$('image_tblheader').style.display = '';
					}
					$(file.id).style.display = 'none';
				}
			} else {
				showDialog('上传失败', 'notice', null, null, 0, null, null, null, null, sdCloseTime);
				progress.setStatus("Cancelled");
				this.uploader.cancelFile(file);
				progress.setCancelled();
				progress.toggleCancel(true, this.uploader);
			}
		} else {
			progress.setComplete();
			progress.setStatus("上传完成.");
			progress.toggleCancel(false);
		}
	} catch(ex) {
		showDialog(ex, 'notice', null, null, 0, null, null, null, null, sdCloseTime);
	}
}

function uploadComplete(file) {
	IMAGEUPLOADING = 0;
}

function uploadError(file, message) {
	var progress = new FileProgress(file, this.customSettings.progressTarget);
	progress.setStatus("Upload Failed.");
	progress.setCancelled();
}

function getInsertTdId(boxObj, tdId) {
	var tableObj = boxObj.getElementsByTagName("table");
	var tbodyObj, trObj, tdObj;
	if (!tableObj.length) {
		tableObj = document.createElement("table");
		tableObj.className = "imgl";
		tbodyObj = document.createElement("TBODY");
		tableObj.appendChild(tbodyObj);
		boxObj.appendChild(tableObj);

	} else if (!tableObj[0].getElementsByTagName("tbody").length) {
		tbodyObj = document.createElement("TBODY");
		tableObj.appendChild(tbodyObj);
	} else {
		tableObj = tableObj[0];
		tbodyObj = tableObj.getElementsByTagName("tbody")[0];
	}

	var createTr = true;
	var inserID = 0;
	if (tbodyObj.childNodes.length) {
		trObj = tbodyObj.childNodes[tbodyObj.childNodes.length - 1];
		var findObj = trObj.getElementsByTagName("TD");
		for (var j = 0; j < findObj.length; j++) {
			if (findObj[j].id == "") {
				inserID = j;
				tdObj = findObj[j];
				break;
			}
		}
		if (inserID) {
			createTr = false;
		}
	}
	if (createTr) {
		trObj = document.createElement("TR");
		for (var i = 0; i < 4; i++) {
			var newTd = document.createElement("TD");
			newTd.width = "25%";
			newTd.vAlign = "bottom";
			newTd.appendChild(document.createTextNode(" "));
			trObj.appendChild(newTd);
		}
		tdObj = trObj.childNodes[0];
		tbodyObj.appendChild(trObj);
	}
	tdObj.id = tdId;
	return tdObj;
}

function downremoteblog() {
	if(DOWNREMOTESTATUS > 0){
		return false;
	}
	edit_save();
	var message = $('uchome-ttHtmlEditor').value;

	var imgtemp = [];
    message.replace(/<img [^>]*src=[\'\"]([^\'\"]+)[^>]*>/gi, function (match, capture) {
		var imghost = getHost(capture);
		if(capture && imghost != "null" && !in_array(imghost, h5whitedomain)){
        	imgtemp.push(capture);
		}
    }).replace(/.*background[^;\"]+url\([\'\"]?([^\'\"\)]+)[\'\"]?\).*/gi, function (match, capture) {
		var imghost = getHost(capture);
		if(capture && imghost != "null" && !in_array(imghost, h5whitedomain)){
        	imgtemp.push(capture);
		}
	});
	var downurl = 'plugin.php?id=h5upload:downremote&operation=blog&inajax=1';
	if(imgtemp.length > 0){
		DOWNREMOTESTATUS = 1;
		showDialog('<div id="remotedowninfo"><p class="mbn">远程图片正在下载中（<span id="downRemoteAlready">0</span>/'+imgtemp.length+'）</p><p><img src="' + STATICURL + 'image/common/uploading.gif" alt="" /></p></div>', 'notice', '', null, 1);		
		function downremoteblogimg(i) {
			if(i < imgtemp.length){
				var imgurl = imgtemp[i];
				var formObj = $("articleform");
				var aid = formObj.elements['aid'].value;
				var catid = formObj.elements['catid'].value;
				var x = new Ajax();
				x.post(downurl, 'aid='+aid+'&catid='+catid+'&imgurl=' + escape(imgurl), function(data){
					if (data) {
						data = eval('(' + data + ')');

						var tdObj = getInsertTdId($('imgattachlist'), 'attach_list_' + data.aid);
						ajaxget('portal.php?mod=attachment&op=getattach&id=' + data.aid, tdObj.id, null, '', null, function(){});

						$('attach_ids').value += ',' + data.aid;
						if($('conver').value == '') {
							$('conver').value = data.cover;
						}
						if($('imageuploaderdndtip')) {
							$('imageuploaderdndtip').style.display = 'none';
						}

    					message = message.replace(/<img [^>]*src=[\'\"]([^\'\"]+)[^>]*>/gi, function (match, capture) {
								if(capture == imgurl){
									return match.replace(capture, data.bigimg);
								}else{
									return match;
								}
    					}).replace(/.*background[^;\"]+url\([\'\"]?([^\'\"\)]+)[\'\"]?\).*/gi, function (match, capture) {
								if(capture == imgurl){
									return match.replace(capture, data.bigimg);
								}else{
									return match;
								}
    					});

						var f = window.frames["uchome-ifrHtmlEditor"].window.frames["HtmlEditor"];
						f.document.body.innerHTML = message;

						if($('downRemoteAlready')){
							$('downRemoteAlready').innerHTML = i+1;
							downremoteblogimg(i+1);
						}else{
							DOWNREMOTESTATUS = 0;
						}

					}else{
    					message = message.replace(/<img [^>]*src=[\'\"]([^\'\"]+)[^>]*>/gi, function (match, capture) {
								if(capture == imgurl){
									return '';
								}else{
									return match;
								}
    					}).replace(/.*background[^;\"]+url\([\'\"]?([^\'\"\)]+)[\'\"]?\).*/gi, function (match, capture) {
								if(capture == imgurl){
									return '';
								}else{
									return match;
								}
    					});
						var f = window.frames["uchome-ifrHtmlEditor"].window.frames["HtmlEditor"];
						f.document.body.innerHTML = message;


						if($('downRemoteAlready')){
							$('downRemoteAlready').innerHTML = i+1;
							downremoteblogimg(i+1);
						}else{
							DOWNREMOTESTATUS = 0;
						}

					}

				});
			}else{
				DOWNREMOTESTATUS = 0;
				hideMenu('fwin_dialog', 'dialog');
			}
		}
		downremoteblogimg(0);
	}
}

function downremoteportal() {
	if(typeof check_catid == 'function' && !check_catid()) {
		return false;
	}
	if(DOWNREMOTESTATUS > 0){
		return false;
	}
	edit_save();
	var message = $('uchome-ttHtmlEditor').value;

	var imgtemp = [];
    message.replace(/<img [^>]*src=[\'\"]([^\'\"]+)[^>]*>/gi, function (match, capture) {
		var imghost = getHost(capture);
		if(capture && imghost != "null" && !in_array(imghost, h5whitedomain)){
        	imgtemp.push(capture);
		}
    }).replace(/.*background[^;\"]+url\([\'\"]?([^\'\"\)]+)[\'\"]?\).*/gi, function (match, capture) {
		var imghost = getHost(capture);
		if(capture && imghost != "null" && !in_array(imghost, h5whitedomain)){
        	imgtemp.push(capture);
		}
	});
	var downurl = 'plugin.php?id=h5upload:downremote&operation=portal&inajax=1';
	if(imgtemp.length > 0){
		DOWNREMOTESTATUS = 1;
		showDialog('<div id="remotedowninfo"><p class="mbn">远程图片正在下载中（<span id="downRemoteAlready">0</span>/'+imgtemp.length+'）</p><p><img src="' + STATICURL + 'image/common/uploading.gif" alt="" /></p></div>', 'notice', '', null, 1);		
		function downremoteportalimg(i) {
			if(i < imgtemp.length){
				var imgurl = imgtemp[i];
				var formObj = $("articleform");
				var aid = formObj.elements['aid'].value;
				var catid = formObj.elements['catid'].value;
				var x = new Ajax();
				x.post(downurl, 'aid='+aid+'&catid='+catid+'&imgurl=' + escape(imgurl), function(data){
					if (data) {
						data = eval('(' + data + ')');

						var tdObj = getInsertTdId($('imgattachlist'), 'attach_list_' + data.aid);
						ajaxget('portal.php?mod=attachment&op=getattach&id=' + data.aid, tdObj.id, null, '', null, function(){});

						$('attach_ids').value += ',' + data.aid;
						if($('conver').value == '') {
							$('conver').value = data.cover;
						}
						if($('imageuploaderdndtip')) {
							$('imageuploaderdndtip').style.display = 'none';
						}
						if ($('image_tblheader')) {
							$('image_tblheader').style.display = '';
						}
    					message = message.replace(/<img [^>]*src=[\'\"]([^\'\"]+)[^>]*>/gi, function (match, capture) {
								if(capture == imgurl){
									return match.replace(capture, data.bigimg);
								}else{
									return match;
								}
    					}).replace(/.*background[^;\"]+url\([\'\"]?([^\'\"\)]+)[\'\"]?\).*/gi, function (match, capture) {
								if(capture == imgurl){
									return match.replace(capture, data.bigimg);
								}else{
									return match;
								}
    					});

						var f = window.frames["uchome-ifrHtmlEditor"].window.frames["HtmlEditor"];
						f.document.body.innerHTML = message;

						if($('downRemoteAlready')){
							$('downRemoteAlready').innerHTML = i+1;
							downremoteportalimg(i+1);
						}else{
							DOWNREMOTESTATUS = 0;
						}

					}else{
    					message = message.replace(/<img [^>]*src=[\'\"]([^\'\"]+)[^>]*>/gi, function (match, capture) {
								if(capture == imgurl){
									return '';
								}else{
									return match;
								}
    					}).replace(/.*background[^;\"]+url\([\'\"]?([^\'\"\)]+)[\'\"]?\).*/gi, function (match, capture) {
								if(capture == imgurl){
									return '';
								}else{
									return match;
								}
    					});
						var f = window.frames["uchome-ifrHtmlEditor"].window.frames["HtmlEditor"];
						f.document.body.innerHTML = message;

						if($('downRemoteAlready')){
							$('downRemoteAlready').innerHTML = i+1;
							downremoteportalimg(i+1);
						}else{
							DOWNREMOTESTATUS = 0;
						}

					}

				});
			}else{
				DOWNREMOTESTATUS = 0;
				hideMenu('fwin_dialog', 'dialog');
			}
		}
		downremoteportalimg(0);
	}
}

function downremoteforum() {
	if(DOWNREMOTESTATUS > 0){
		return false;
	}
	var message = wysiwyg ? html2bbcode(getEditorContents()) : (!editorform.parseurloff.checked ? parseurl(editorform.message.value) : editorform.message.value);
	var imgtemp = [];
	if(!fetchCheckbox('bbcodeoff')) {
		message.replace(/\[img\]\s*([^\[\<\r\n]+?)\s*\[\/img\]|\[img=\d{1,4}[x|\,]\d{1,4}\]\s*([^\[\<\r\n]+?)\s*\[\/img\]/gi, function (match, capture1, capture2) {
			var capture = capture1 ? capture1 : capture2;
			var imghost = getHost(capture);
			if(capture && imghost != "null" && !in_array(imghost, h5whitedomain)){
        		imgtemp.push(capture);
			}
    	});
	}
	if(allowhtml && fetchCheckbox('htmlon')) {
		message.replace(/<img [^>]*src=[\'\"]([^\'\"]+)[^>]*>/gi, function (match, capture) {
			var imghost = getHost(capture);
			if(capture && imghost != "null" && !in_array(imghost, h5whitedomain)){
        		imgtemp.push(capture);
			}
    	});
	}
	var downurl = 'plugin.php?id=h5upload:downremote&operation=forum&inajax=1';
	if(imgtemp.length > 0){
		DOWNREMOTESTATUS = 1;
		showDialog('<div id="remotedowninfo"><p class="mbn">远程图片正在下载中（<span id="downRemoteAlready">0</span>/'+imgtemp.length+'）</p><p><img src="' + STATICURL + 'image/common/uploading.gif" alt="" /></p></div>', 'notice', '', null, 1);		
		function downremoteforumimg(i) {
			if(i < imgtemp.length){
				var imgurl = imgtemp[i];
				var x = new Ajax();
				x.post(downurl+'&fid='+fid, 'imgurl=' + escape(imgurl), function(s){
					var aid = parseInt(s);
					if(aid){
						ajaxget('forum.php?mod=ajax&action=imagelist&pid=' + pid + '&posttime=' + $('posttime').value + (!fid ? '' : '&fid=' + fid), 'imgattachlist', null, null, null, function(){
							message = wysiwyg ? html2bbcode(getEditorContents()) : (!editorform.parseurloff.checked ? parseurl(editorform.message.value) : editorform.message.value);
							if(!fetchCheckbox('bbcodeoff')) {
    							message = message.replace(/\[img\]\s*([^\[\<\r\n]+?)\s*\[\/img\]|\[img=\d{1,4}[x|\,]\d{1,4}\]\s*([^\[\<\r\n]+?)\s*\[\/img\]/gi, function (match, capture1, capture2) {
									var capture = capture1 ? capture1 : capture2;
									if(capture == imgurl){
										return '[attachimg]'+aid+'[/attachimg]';
									}else{
										return match;
									}
    							});
							}
							if(allowhtml && fetchCheckbox('htmlon')) {
								message = message.replace(/<img [^>]*src=[\'\"]([^\'\"]+)[^>]*>/gi, function (match, capture) {
									if(capture == imgurl){
										return '[attachimg]'+aid+'[/attachimg]';
									}else{
										return match;
									}
    							});
							}
							if(wysiwyg) {
								message = bbcode2html(message);
								writeEditorContents(message);
							} else {
								textobj.value = message;
							}
							if($('imageuploaderdndtip')) {
								$('imageuploaderdndtip').style.display = 'none';
							}
							if ($('image_tblheader')) {
								$('image_tblheader').style.display = '';
							}
							switchImagebutton('imgattachlist');
							$('imgattach_notice').style.display = '';
							if($('downRemoteAlready')){
								$('downRemoteAlready').innerHTML = i+1;
								downremoteforumimg(i+1);
							}else{
								DOWNREMOTESTATUS = 0;
							}
						});
					}else{
						message = wysiwyg ? html2bbcode(getEditorContents()) : (!editorform.parseurloff.checked ? parseurl(editorform.message.value) : editorform.message.value);
						if(!fetchCheckbox('bbcodeoff')) {
    						message = message.replace(/\[img\]\s*([^\[\<\r\n]+?)\s*\[\/img\]|\[img=\d{1,4}[x|\,]\d{1,4}\]\s*([^\[\<\r\n]+?)\s*\[\/img\]/gi, function (match, capture) {
								if(capture == imgurl){
									return '';
								}else{
									return match;
								}
    						});
						}
						if(allowhtml && fetchCheckbox('htmlon')) {
							message = message.replace(/<img [^>]*src=[\'\"]([^\'\"]+)[^>]*>/gi, function (match, capture) {
								if(capture == imgurl){
									return '';
								}else{
									return match;
								}
    						});
						}
						if(wysiwyg) {
							editdoc.body.innerHTML = message;
							switchEditor(0);
							switchEditor(1);
						} else {
							textobj.value = message;
						}
						if($('downRemoteAlready')){
							$('downRemoteAlready').innerHTML = i+1;
							downremoteforumimg(i+1);
						}else{
							DOWNREMOTESTATUS = 0;
						}
					}
				})
			}else{
				DOWNREMOTESTATUS = 0;
				hideMenu('fwin_dialog', 'dialog');
			}
		}
		downremoteforumimg(0);
	}
}

function updateDownImageList(msg) {
	hideMenu('fwin_dialog', 'dialog');
	if(msg == '') {
		showError('抱歉，暂无远程附件');
	} else {
		ajaxget('forum.php?mod=ajax&action=imagelist&pid=' + pid + '&posttime=' + $('posttime').value + (!fid ? '' : '&fid=' + fid), 'imgattachlist', null, null, null, function(){
			if(wysiwyg) {
				editdoc.body.innerHTML = msg;
				switchEditor(0);
				switchEditor(1);
			} else {
				textobj.value = msg;
			}
			if($('imageuploaderdndtip')) {
				$('imageuploaderdndtip').style.display = 'none';
			}
		});
		switchImagebutton('imgattachlist');
		$('imgattach_notice').style.display = '';
		showDialog('远程附件下载完成!', 'right', null, null, 0, null, null, null, null, 3);
	}
}

function updateAttachList(action, aids) {
	ajaxget('forum.php?mod=ajax&action=attachlist' + (!action ? '&posttime=' + $('posttime').value : (!aids ? '' : '&aids=' + aids)) + (!fid ? '' : '&fid=' + fid), 'attachlist');
	switchAttachbutton('attachlist');
	$('attach_tblheader').style.display = $('attach_notice').style.display = '';
	if($('attachuploaderdndtip')) {
		$('attachuploaderdndtip').style.display = 'none';
	}
}

function updateImageList(action, aids) {
	ajaxget('forum.php?mod=ajax&action=imagelist' + (!action ? '&pid=' + pid + '&posttime=' + $('posttime').value : (!aids ? '' : '&aids=' + aids)) + (!fid ? '' : '&fid=' + fid), 'imgattachlist', null, '', '', function() {
		if ($('imgattachlist').innerHTML != '') {
			if ($('image_tblheader')) {
				$('image_tblheader').style.display = '';
			}
			$('imgattach_notice').style.display = '';
			if($('imageuploaderdndtip')) {
				$('imageuploaderdndtip').style.display = 'none';
			}
		}
	});
	switchImagebutton('imgattachlist');
}

function serializeData(data){
	var parts = [];
	for(key in data){
		parts.push(encodeURIComponent(key) + "=" + encodeURIComponent(data[key]));
	}
	return parts.join("&");
}

function insertAllImageTag(order) {
	var attachListObj = $('imgattachlist').parentNode.getElementsByTagName("td");
	var length = attachListObj.length;
	for(var i in attachListObj) {
		var k = order ? length - i - 1 : i;
		var attach = attachListObj[k];
		if(typeof attach == "object" && attach.id) {
			var ids = attach.id.split('_');
			if(ids[0] == 'image') {
				if($('imageattach'+ids[2]) && !hasClass(attach, 'imgdeleted')) {
					insertAttachimgTag(ids[2]);
					var txt = wysiwyg ? '\r\n<br/>\r\n' : '\r\n';
					insertText(txt, strlen(txt), 0);
				}
			}
		}
	}
	doane();
}

function insertAllImagePortal(order) {
	var attachListObj = $('imgattachlist').getElementsByTagName("td");
	var length = attachListObj.length;
	for(var i in attachListObj) {
		var k = order ? length - i - 1 : i;
		var attach = attachListObj[k];
		if(typeof attach == "object") {
			var ids = attach.id.split('_');
			if(attach.style.display != 'none') {
				var attachimgitem = attach.getElementsByTagName("img");
				if(attachimgitem.length > 0){
					attachimgitem[0].click();
				}
			}
		}
	}
	doane();
}

function deleteAllImageTag() {
	var attachListObj = $('imgattachlist').parentNode.getElementsByTagName("td");
	var length = attachListObj.length;
	for(var i in attachListObj) {
		if(typeof attachListObj[i] == "object") {
			var attach = attachListObj[i];
			var ids = attach.id.split('_');
			if(ids[0] == 'image') {
				if($('imageattach'+ids[2]) && !hasClass(attach, 'imgdeleted')) {
					delImgAttach(parseInt(ids[2]),1);
				}
			}
		}
	}
	doane();
}

function deleteAllImagePortal() {
	var attachListObj = $('imgattachlist').getElementsByTagName("td");
	var length = attachListObj.length;
	for(var i in attachListObj) {
		if(typeof attachListObj[i] == "object") {
			var attach = attachListObj[i];
			var ids = attach.id.split('_');
			if(attach.style.display != 'none') {
				var attachimgitem = attach.getElementsByTagName("img");
				if(attachimgitem.length > 0){
					deleteAttach(ids[2], 'portal.php?mod=attachment&id='+ids[2]+'&op=delete');
				}
			}
		}
	}
	doane();
}

function uploadqrcodeforum(fid){
	var eles = $(editorid + '_imgattachlist').getElementsByClassName("notice");
	if(eles.length == 0){
		return false;
	}
	var noticebox = eles[0];
	noticebox.outerHTML = '<div style="padding-right:60px;position:relative;">'+noticebox.outerHTML+'<div class="notice upnf">温馨提示：扫描右侧二维码可以将手机中的图片上传到这里</div><div style="position:absolute; right:0; bottom:-8px;"><img src="plugin.php?id=h5upload:qrupload&operation=forum&qrcode=1&fid=' + fid +'" id="uploadqrcode" class="miniqrcode"></div></div>';
	$('uploadqrcode').onclick = function() {
		if(hasClass(this, 'zoomqrcode')){
			this.className = 'miniqrcode';
		}else{
			this.className = 'zoomqrcode';
		}
	};
	//每隔一小时自动刷新二维码
	setInterval(function(){
		$('uploadqrcode').src = 'plugin.php?id=h5upload:qrupload&operation=portal&qrcode=1&fid=' + fid +'&_r='+Math.random();
	}, 3600*1000);
	//自动刷新图片上传列表
	setInterval(function(){
		if(IMAGEUPLOADING){
			return false;
		}
		if($(editorid+'_image_menu').style.display != 'none' && $(editorid+'_image_menu').style.visibility != 'hidden' && $(editorid+'_imgattachlist').style.display != 'none'){
			//updateImageList($('attachnotice_img') && $('attachnotice_img').style.display == 'none');
			var x = new Ajax();
			x.getJSON('plugin.php?id=h5upload:qrupload&operation=forumpic&pid=' + pid + '&posttime=' + $('posttime').value, function(s) {
				if(s !== null) {
					var aids = s.aids;
					for (var i in aids) {
						var aidexist = false;
						var attachListObj = $('imgattachlist').getElementsByTagName("td");
						for(var j in attachListObj) {
							if(typeof attachListObj[j] == "object") {
								var attach = attachListObj[j];
								var ids = attach.id.split('_');
								if(ids[2] == aids[i]) {
									aidexist = true;
									break;
								}
							}
						}
						if(!aidexist) {
							var tdObj = getInsertTdId($('imgattachlist'), 'image_td_' + aids[i]);
							ajaxget('forum.php?mod=ajax&action=imagelist&type=single&pid=' + pid + '&aids=' + aids[i] + (!fid ? '': '&fid=' + fid), tdObj.id, null, '', null, function(){});
							if($('imageuploaderdndtip')) {
								$('imageuploaderdndtip').style.display = 'none';
							}
							if ($('image_tblheader')) {
								$('image_tblheader').style.display = '';
							}
						}
					}
				}
			});
		}
	}, 3000);
}

function uploadqrcodeportal(aid, catid){
	var eles = $('icoImg_imgattachlist').getElementsByClassName("notice");
	if(eles.length == 0){
		return false;
	}
	var noticebox = eles[0];
	noticebox.outerHTML = '<div style="padding-right:60px;position:relative;">'+noticebox.outerHTML+'<div class="notice upnf">温馨提示：扫描右侧二维码可以将手机中的图片上传到这里</div><div style="position:absolute; right:0; bottom:-8px;"><img src="plugin.php?id=h5upload:qrupload&operation=portal&qrcode=1&aid=' + aid +'&catid=' + catid +'" id="uploadqrcode" class="miniqrcode"></div></div>';
	$('uploadqrcode').onclick = function() {
		if(hasClass(this, 'zoomqrcode')){
			this.className = 'miniqrcode';
		}else{
			this.className = 'zoomqrcode';
		}
	};
	//每隔一小时自动刷新二维码
	setInterval(function(){
		$('uploadqrcode').src = 'plugin.php?id=h5upload:qrupload&operation=portal&qrcode=1&aid=' + aid +'&catid=' + catid +'&_r='+Math.random();
	}, 3600*1000);
	//自动刷新图片上传列表
	setInterval(function(){
		if(IMAGEUPLOADING){
			return false;
		}
		if($('icoImg_image_menu').style.display != 'none' && $('icoImg_image_menu').style.visibility != 'hidden' && $('icoImg_imgattachlist').style.display != 'none'){
			var x = new Ajax();
			x.getJSON('plugin.php?id=h5upload:qrupload&operation=portalpic&aid=' + aid +'&posttime=' + $('posttime').value, function(s) {
				if(s !== null) {
					var aids = s.aids;
					for (var i in aids) {
						var aidexist = false;
						var attachListObj = $('imgattachlist').getElementsByTagName("td");
						for(var j in attachListObj) {
							if(typeof attachListObj[j] == "object") {
								var attach = attachListObj[j];
								var ids = attach.id.split('_');
								if(ids[2] == aids[i]) {
									aidexist = true;
									break;
								}
							}
						}
						if(!aidexist) {
							var tdObj = getInsertTdId($('imgattachlist'), 'attach_list_' + aids[i]);
							ajaxget('portal.php?mod=attachment&op=getattach&id=' + aids[i], tdObj.id, null, '', null, function(){});
							if($('imageuploaderdndtip')) {
								$('imageuploaderdndtip').style.display = 'none';
							}
							if ($('image_tblheader')) {
								$('image_tblheader').style.display = '';
							}
						}
					}
				}
			});
		}
	}, 3000);
}

function FileProgress(file, targetID) {
	this.fileProgressID = file.id;

	this.opacity = 100;
	this.height = 0;

	this.fileProgressWrapper = document.getElementById(this.fileProgressID);
	if (!this.fileProgressWrapper) {
		this.fileProgressWrapper = document.createElement("div");
		this.fileProgressWrapper.className = "progressWrapper";
		this.fileProgressWrapper.id = this.fileProgressID;

		this.fileProgressElement = document.createElement("div");
		this.fileProgressElement.className = "progressContainer";

		var progressCancel = document.createElement("a");
		progressCancel.className = "progressCancel";
		progressCancel.href = "javascript:;";
		progressCancel.style.display = "none";
		progressCancel.appendChild(document.createTextNode(" "));

		var progressText = document.createElement("div");
		progressText.className = "progressName";
		progressText.appendChild(document.createTextNode(file.name));

		var progressBar = document.createElement("div");
		progressBar.className = "progressBarInProgress";

		var progressStatus = document.createElement("div");
		progressStatus.className = "progressBarStatus";
		progressStatus.innerHTML = "&nbsp;";

		this.fileProgressElement.appendChild(progressCancel);
		this.fileProgressElement.appendChild(progressText);
		this.fileProgressElement.appendChild(progressStatus);
		this.fileProgressElement.appendChild(progressBar);

		this.fileProgressWrapper.appendChild(this.fileProgressElement);

		document.getElementById(targetID).appendChild(this.fileProgressWrapper);
	} else {
		this.fileProgressElement = this.fileProgressWrapper.firstChild;
		this.reset();
	}

	this.height = this.fileProgressWrapper.offsetHeight;
	this.setTimer(null);

}

FileProgress.prototype.setTimer = function(timer) {
	this.fileProgressElement["FP_TIMER"] = timer;
};
FileProgress.prototype.getTimer = function(timer) {
	return this.fileProgressElement["FP_TIMER"] || null;
};

FileProgress.prototype.reset = function() {
	try {
		this.fileProgressElement.className = "progressContainer";

		this.fileProgressElement.childNodes[2].innerHTML = "&nbsp;";
		this.fileProgressElement.childNodes[2].className = "progressBarStatus";

		this.fileProgressElement.childNodes[3].className = "progressBarInProgress";
		this.fileProgressElement.childNodes[3].style.width = "0%";

		this.appear();
	} catch(ex) {}
};

FileProgress.prototype.setProgress = function(percentage) {
	this.fileProgressElement.className = "progressContainer green";
	this.fileProgressElement.childNodes[3].className = "progressBarInProgress";
	this.fileProgressElement.childNodes[3].style.width = percentage + "%";

	this.appear();
};
FileProgress.prototype.setComplete = function() {
	this.fileProgressElement.className = "progressContainer blue";
	this.fileProgressElement.childNodes[3].className = "progressBarComplete";
	this.fileProgressElement.childNodes[3].style.width = "";

};
FileProgress.prototype.setError = function() {
	this.fileProgressElement.className = "progressContainer red";
	this.fileProgressElement.childNodes[3].className = "progressBarError";
	this.fileProgressElement.childNodes[3].style.width = "";

	var oSelf = this;
	this.setTimer(setTimeout(function() {
		oSelf.disappear();
	},
	5000));
};
FileProgress.prototype.setCancelled = function() {
	this.fileProgressElement.className = "progressContainer";
	this.fileProgressElement.childNodes[3].className = "progressBarError";
	this.fileProgressElement.childNodes[3].style.width = "";

	var oSelf = this;
	this.setTimer(setTimeout(function() {
		oSelf.disappear();
	},
	2000));
};
FileProgress.prototype.setStatus = function(status) {
	this.fileProgressElement.childNodes[2].innerHTML = status;
};

FileProgress.prototype.toggleCancel = function(show, swfUploadInstance) {
	this.fileProgressElement.childNodes[0].style.display = show ? "": "none";
	if (swfUploadInstance) {
		var fileID = this.fileProgressID;
		var oSelf = this;
		this.fileProgressElement.childNodes[0].onclick = function() {
			swfUploadInstance.cancelFile(fileID);
			oSelf.setStatus("Cancelled");
			oSelf.setCancelled();
			return false;
		};
	}
};

FileProgress.prototype.appear = function() {
	if (this.getTimer() !== null) {
		clearTimeout(this.getTimer());
		this.setTimer(null);
	}

	if (this.fileProgressWrapper.filters) {
		try {
			this.fileProgressWrapper.filters.item("DXImageTransform.Microsoft.Alpha").opacity = 100;
		} catch(e) {
			this.fileProgressWrapper.style.filter = "progid:DXImageTransform.Microsoft.Alpha(opacity=100)";
		}
	} else {
		this.fileProgressWrapper.style.opacity = 1;
	}

	this.fileProgressWrapper.style.height = "";

	this.height = this.fileProgressWrapper.offsetHeight;
	this.opacity = 100;
	this.fileProgressWrapper.style.display = "";

};

FileProgress.prototype.disappear = function() {

	var reduceOpacityBy = 15;
	var reduceHeightBy = 4;
	var rate = 30; // 15 fps
	if (this.opacity > 0) {
		this.opacity -= reduceOpacityBy;
		if (this.opacity < 0) {
			this.opacity = 0;
		}

		if (this.fileProgressWrapper.filters) {
			try {
				this.fileProgressWrapper.filters.item("DXImageTransform.Microsoft.Alpha").opacity = this.opacity;
			} catch(e) {
				this.fileProgressWrapper.style.filter = "progid:DXImageTransform.Microsoft.Alpha(opacity=" + this.opacity + ")";
			}
		} else {
			this.fileProgressWrapper.style.opacity = this.opacity / 100;
		}
	}

	if (this.height > 0) {
		this.height -= reduceHeightBy;
		if (this.height < 0) {
			this.height = 0;
		}

		this.fileProgressWrapper.style.height = this.height + "px";
	}

	if (this.height > 0 || this.opacity > 0) {
		var oSelf = this;
		this.setTimer(setTimeout(function() {
			oSelf.disappear();
		},
		rate));
	} else {
		this.fileProgressWrapper.style.display = "none";
		this.setTimer(null);
	}
};
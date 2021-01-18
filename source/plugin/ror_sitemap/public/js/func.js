var Func = {
	
	ajax : function(d){		
		if(d.confirm){
			layer.confirm('确定继续执行此操作吗?',{icon:3, title:'提示信息'},function(index){
				Func.ajaxed(d);
				
				layer.close(index);
			});
		}else{
			Func.ajaxed(d);
		}
	},
	
	ajaxed : function(d){
		var url = d.url;
		d.url = null;

		var index = layer.msg('加载中，请稍候...',{icon: 16,time:false,shade:0.3});
		
		$.ajax({
			data    : d,
			dataType: 'html',
			type    : 'post',
			url     : url,
			success : function(data)
			{
				var rule = /\{(.*?)\}/;
				data = data.match(rule);
				data = JSON.parse(data[0]);
				
				layer.close(index);
				
				if(data.state != 0){
					layer.msg(data.result);
					return;
				}
				
				if(data.result){
					layer.msg(data.result);
				}

				location.reload();
			}
		});
	},

	window : function(d)
	{
		var config = {
			title : 'window',
		};
		
		config = $.extend(config, d);
			
		index = layer.open({
			title : config.title,
			type : 2,
			content : config.url,
			maxmin: true,
			area: ['900px', '600px']
		})
		
		//改变窗口大小时，重置弹窗的高度，防止超出可视区域（如F12调出debug的操作）
		$(window).resize(function(){
			layer.full(index);
		});
		
		layer.full(index);
	},
	
	open : function(d)
	{
		var config = {
			title : 'window',
		};
		
		config = $.extend(config, d);
			
		index = layer.open({
			title : config.title,
			type : 2,
			content : config.url,
			maxmin: true,
			area: ['900px', '600px']
		});
	},
	
	post : function(d)
	{
		setTimeout(function(){Func.posted(d);}, 100);
	},
	
	posted : function(d)
	{
		d.formid = d.formid?d.formid:'form';
		var f = $('#'+d.formid);f.attr('action');
		d.url = d.url?d.url:f.attr('action');

		if($('.layui-input-block').children().hasClass('layui-form-danger')){
			return;
		}
		
		var index = layer.msg('加载中，请稍候...',{icon: 16,time:false,shade:0.3});
		
		$.ajax({
			data    : f.serialize(),
			dataType: 'html',
			type    : 'post',
			url     : d.url,
			success : function(data){
				
				var rule = /\{(.*?)\}/;
				data = data.match(rule);
				data = JSON.parse(data[0]);
				
				layer.close(index);
				
				if(data.state == 1){
					Func.notice(data.result);
					return;
				}
				
				if(data.result){
					Func.notice(data.result);
				}
				
				if(data.callreload){
					setTimeout(function(){parent.location.reload();}, 1000); 
					return;
				}else if(data.url){
					setTimeout(function(){location.href = data.url;}, 1000); 
					return;
				}else if(data.reload){
					setTimeout(function(){location.reload();}, 1000); 
					return;
				}else if(d.callback){
					d.callback(data);
				}else if(data.callurl){
					setTimeout(function(){parent.location.href = data.callurl;}, 1000); 
					return;
				}else if(data.pcallreload){
					setTimeout(function(){parent.parent.location.reload();}, 1000); 
					return;
				}
			}
		});
	},
	
	load : function(type)
	{
		if(type){
			index = layer.load();
		}else{
			layer.close(index);     
		}
	},
	
	notice : function(msg){
		layer.msg(msg);
	}
}



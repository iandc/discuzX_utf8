var General = {
		
	jquery : function(callback)
	{
		var init_count = 0;
		var init_intval = window.setInterval(function(){
			if(window.jQuery){
				jQuery.noConflict();
				clearInterval(init_intval);
				if(typeof callback == 'function'){
					callback();
				} 
			}else{
				if(init_count > 10){
					window.clearInterval(init_intval);
				}
				init_count++;
			}
		}, 300);
	},
	
	loading : function(type)
	{ 
		if(jQuery('#loading').length > 0){
			if(type == 1){
				jQuery('#loading').show().css('top',(jQuery(document).scrollTop()+jQuery(window).height()/2)+'px');
			}else{
				jQuery('#loading').hide();
			}
		}else{
			jQuery('body').append('<img src="static/image/common/uploading.gif" style="position:absolute;z-index:999;top:'+(jQuery(document).scrollTop()+jQuery(window).height()/2)+'px;left:'+(jQuery(window).width()/2 - 30)+'px" id="loading"/>');
		}
	},

	notice : function(msg)
	{ 
		jQuery('body').append('<div style="padding:10px;width:150px;text-align:center;color:#fff;background:rgba(0,0,0,.68);-webkit-border-radius:5px;border-radius:5px;position:absolute;z-index:999;top:'+(jQuery(document).scrollTop()+jQuery(window).height()/2)+'px;left:'+(jQuery(window).width()/2 - 75)+'px" id="mynotice">'+msg+'</div>');
		setTimeout(function(){
			jQuery('#mynotice').remove();
		}, 2000);
	},
	
	date: function(unixTime, isFull, timeZone)
	{
	    if(typeof (timeZone) == 'number'){
	    	unixTime = parseInt(unixTime) + parseInt(timeZone) * 60 * 60;
	    }
	    
	    var time = new Date(unixTime * 1000);
	    var ymdhis = '';
	    ymdhis += time.getUTCFullYear()+'-';
	    ymdhis += (time.getUTCMonth()+1)+'-';
	    ymdhis += time.getUTCDate();
	    
	    if(isFull === true){
			ymdhis += ' '+time.getUTCHours()+':';
			ymdhis += time.getUTCMinutes()+':';
			ymdhis += time.getUTCSeconds();
	    }
	    
	    return ymdhis;
	}
};
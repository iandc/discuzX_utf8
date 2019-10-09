var phonematch = /^1[3|4|5|6|7|8|9][0-9]\d{8}$/;
var sendsmsUrl = 'plugin.php?id=qxt_login&action=sendsms';
var regUrl = 'member.php?mobile=2&inajax=1';
var mobileLoginUrl = 'plugin.php?id=qxt_login&loginsubmit=mobile&inajax=1';
var userLoginUrl = 'member.php?mod=logging&action=login&loginsubmit=yes&mobile=2&inajax=1';
$(function() {
    'use strict';
	
	//注册页面
    $(document).on("pageInit", "#page-reg",
    function(e, id, page) {
		var seccodehash = $("input[name='seccodehash']").val();
		var formhash = $("input[name='formhash']").val();
		
		$(page).on('click', '.seccodeimg',
        function() {
			var ran = $("input[name='ran']").val();
			$('#seccodeverify_' + seccodehash).attr('value', '');
			var tmprandom = 'S' + Math.floor(Math.random() * 1000);
			$('.sechash').attr('value', tmprandom);
			$(this).attr('src', 'misc.php?mod=seccode&update='+ ran +'&idhash='+ tmprandom +'&mobile=2');
		});
		$(page).on('click', '.sendsmssec',
        function() {
			var mobile = $("#mobile").val();
			var seccodeverify = $("input[name='seccodeverify']").val();
            if (mobile == "") return $.toast("手机号码不能为空");
            if (!mobile.match(phonematch)) return $.toast("手机号格式有误");
			if (seccodeverify == "") return $.toast("请填写图形验证码");
            $.ajax({
                url: sendsmsUrl,
                data: {
					mobile:mobile,
					seccodeverify:seccodeverify,
					seccodehash:seccodehash,
					formhash:formhash,
					type: 1
				},
                type: 'POST',
                dataType: "json",
                success: function(data) {
                    //console.log(data);
                    if (data.flag == "success") {
                        disable_smsbtn();
                    } else {
                        $.toast(data.message);
                    }
                },
                error: function() {
                    $.toast("请检查网络情况");
                },
                timeout: 4000
            });
		});
		
		$(page).on('click', '.reg-btn',
        function() {
			var mobile = $("#mobile").val();
			var seccodeverify = $("input[name='seccodeverify']").val();
			var smssec = $("input[name='smssec']").val();
			if (mobile == "") return $.toast("手机号码不能为空");
            if (!mobile.match(phonematch)) return $.toast("手机号格式有误");
			if (seccodeverify == "") return $.toast("请填写图形验证码");
			if (smssec == "") return $.toast("请输入短信验证码");
			var formvalue = $('form').serializeArray();
			$.ajax({
                url: regUrl,
                data: formvalue,
                type: 'POST',
                dataType: "xml",
                success: function(data) {
					var msg = $(data).find("root").text();
					var r = msg.match(/<p\>([^\"]*)\<script/);
					if (msg.indexOf("succeedhandle") > 0) {
						$.alert(r[1],
                        function() {
                            window.location.href = '/';
                        });
					}else{
						$.toast(r[1]);
					}                   
                },
                error: function() {
                    $.toast("请检查网络情况");
                },
                timeout: 4000
            });
		});
	});
	
	//登录页面
    $(document).on("pageInit", "#page-login",
    function(e, id, page) {
		$(page).on('click', '.sendsmssec',
        function() {
			var mobile = $("#mobile").val();
			if (mobile == "") return $.toast("手机号码不能为空");
            if (!mobile.match(phonematch)) return $.toast("手机号格式有误");
			$.popup('.popup-seccheck');
		});
		$(page).on('click', '.login-btn',
        function() {
			var formhash = $("input[name='formhash']").val();
			if ($("#tab1").attr("class") == "tab"){
				var username = $("input[name='user']").val();
				var password = $("input[name='pwd']").val();
				if ("" == username.length) return $.toast("用户名不能为空");
				if ("" == password.length) return $.toast("密码不能为空");
				$.ajax({
					url: userLoginUrl,
					data: {
						username:username,
						password:password,
						formhash:formhash
					},
					type: 'POST',
					dataType: "xml",
					success: function(data) {
						var msg = $(data).find("root").text();
						if (msg.indexOf("href") > 0) {
							location.href = '/';
							return;
						}else{
							var r = msg.match(/<p\>([^\"]*)\<script/);
							return $.toast(r[1]);
						}
					},
					error: function() {
						$.toast("请检查网络情况");
					},
					timeout: 4000
				});
			}else{
				var mobile = $("#mobile").val();
				var smssec = $("#smssec").val();
				if (mobile == "") return $.toast("手机号码不能为空");
				if (!mobile.match(phonematch)) return $.toast("手机号格式有误");
				if (smssec == "") return $.toast("短信验证码不能为空");
				if (!smssec.match(/^[0-9]{6}$/)) return $.toast("短信验证码格式有误");
				$.ajax({
					url: mobileLoginUrl,
					data: {
						mobile:mobile,
						smsseccode:smssec,
						formhash:formhash
					},
					type: 'POST',
					dataType: "xml",
					success: function(data) {
						var msg = $(data).find("root").text();
						if (msg.indexOf("succeedhandle") > 0) {
							location.href = '/';
							return;
						}else{
							var r = msg.match(/<p\>([^\"]*)\<script/);
							return $.toast(r[1]);
						}
					},
					error: function() {
						$.toast("请检查网络情况");
					},
					timeout: 4000
				});
			}
		});
	});
	
	$("#page-sec").on('click', '.sec-btn',
    function() {
		var mobile = $("#mobile").val();
		var formhash = $("input[name='formhash']").val();
		var seccodehash = $("input[name='seccodehash']").val();
		var seccodeverify = $("input[name='seccodeverify']").val();
		if (mobile == "") return $.toast("手机号码不能为空");
		if (!mobile.match(phonematch)) return $.toast("手机号格式有误");
		if (seccodeverify == "") return $.toast("请填写图形验证码");
		$.ajax({
			url: sendsmsUrl,
			data: {
				mobile:mobile,
				seccodeverify:seccodeverify,
				seccodehash:seccodehash,
				formhash:formhash,
				type: 3
			},
			type: 'POST',
			dataType: "json",
			success: function(data) {
				if (data.flag == "success") {
					$.closeModal()
					disable_smsbtn();
					//$(".seccodeimg").trigger("click");
				} else {
					$.toast(data.message);
				}
			},
			error: function() {
				$.toast("请检查网络情况");
			},
			timeout: 4000
		});
	});
	$("#page-sec").on('click', '.seccodeimg',
    function() {
		var seccodehash = $("input[name='seccodehash']").val();
		var ran = $("input[name='ran']").val();
		$('#seccodeverify_' + seccodehash).attr('value', '');
		var tmprandom = 'S' + Math.floor(Math.random() * 1000);
		$('.sechash').attr('value', tmprandom);
		$(this).attr('src', 'misc.php?mod=seccode&update='+ ran +'&idhash='+ tmprandom +'&mobile=2');
	});
	
	function disable_smsbtn() {
		var i = $(".sendsmssec"),
		n = 60;
		i.addClass("disabled");
		var t = setInterval(function() {
                n >= 0 ? (i.text(n + "秒后重新发送"), n--, i.attr("disabled", "disabled")) : ( i.text("重发短信验证码"), i.removeClass("disabled"), n = 60, clearInterval(t), i.removeAttr("disabled"))
		},
		1e3);
	}
	
	
    $.init();
});
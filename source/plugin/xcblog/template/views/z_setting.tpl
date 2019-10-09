<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title></title>
  <link rel="stylesheet" href="<%plugin_path%>/template/libs/mwt/4.0/mwt.min.css" type="text/css">
  <link rel="stylesheet" href="<%plugin_path%>/template/views/misadmin.css" type="text/css">
  <script src="<%plugin_path%>/template/libs/jquery/1.11.2/jquery.min.js"></script>
  <script src="<%plugin_path%>/template/libs/mwt/4.0/mwt.min.js"></script>
  <%js_script%>
  <script>
    function appendtip(msg,color) {
        var style= color ? ' style="color:'+color+'"' : '';
        if (color) color = 'style='
        jQuery('#lis').append('<li'+style+'>'+msg+'</li>');
    }
    function checkenv() {
        var env = v.env;
        if (!env.available_plugins['mobile']) {
            appendtip('未安装或启用<a href="http://addon.discuz.com/?@mobile.plugin" target="_blank" style="color:red;">掌上论坛</a>插件','red');
        }
        if (!env.mobile.allowmobile) {
            appendtip('站点未开启手机版访问，<a href="<%siteurl%>/admin.php?frames=yes&action=setting&operation=mobile" target="_blank">点击此处设置</a>','red');
        }
    }
    var jq=jQuery.noConflict();
    jq(document).ready(function($) {
        checkenv();
        jQuery('#home_paper_num').val(v.home_paper_num);
        jQuery('#list_paper_num').val(v.list_paper_num);
    });
  </script>
</head>
<body>
  <form method="post" action="admin.php?action=plugins&operation=config&identifier=xcblog&pmod=z_setting">
  <!-- 使用提示 -->
  <table class="tb tb2">
    <tr><th colspan="15" class="partition">使用提示</th></tr>
    <tr><td class="tipsblock" s="1">
      <ul id="lis">
        <li>博文数据：帖子</li>
        <li>博客地址：<a href="<%siteurl%>/plugin.php?id=xcblog&uid=1" target="_blank"><%siteurl%>/plugin.php?id=xcblog&uid=1</a></li>
      </ul>
    </td></tr>
  </table>
  <!-- 设置 -->
  <table class="tb tb2">
    <tr><th colspan="15" class="partition">设置</th></tr>
    <tr>
      <td width='120'>首页博文数量：</td>
      <td width='200'><input type="number" id="home_paper_num" name="home_paper_num" class="txt" style="width:50%"></td>
      <td class='tips2'>博客首页一页显示的博文个数</td>
    </tr>
	<tr>
	  <td>列表页博文数量：</td>
      <td><input type="number" id="list_paper_num" name="list_paper_num" class="txt" style="width:50%"></td>
	  <td class='tips2'>列表页一页显示的博文个数</td>
	</tr>
    <tr>
      <td colspan="3">
		<input type="hidden" id="reset" name="reset" value="0"/>
        <input type="submit" id='subbtn' class='btn' value="保存设置"/>
        &nbsp;&nbsp;
		<input type="submit" class='btn' onclick="jQuery('#reset').val(1);" value="恢复默认设置"/>
      </td>
    </tr>
  </table>
  </form>
</body>
</html>

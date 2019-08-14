<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_debug.php 1 2014-11-26 08:24:03Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

function debugmessage($ajax = 0) {
    $m = function_exists('memory_get_usage') ? number_format(memory_get_usage()) : '';
    $mt = function_exists('memory_get_peak_usage') ? number_format(memory_get_peak_usage()) : '';
    if($m) {
        $m = '<em>内存占用:</em> <s>'.$m.'</s> bytes'.($mt ? ', 峰值 <s>'.$mt.'</s> bytes' : '').'<br />';
    }
    global $_G;
    $finaltime = dmicrotime() - $_G['starttime'];
    $debugfile = $_G['adminid'] == 1 ? '_debugadmin.php' : '_debug.php';
    $akey = md5(random(10));
    if(!defined('DISCUZ_DEBUG') || !DISCUZ_DEBUG || defined('IN_ARCHIVER') || defined('IN_MOBILE')) {
        return;
    }
    $phpinfok = 'I';
    $viewcachek = 'C';
    $mysqlplek = 'P';
    $includes = get_included_files();
    require_once DISCUZ_ROOT.'./source/discuz_version.php';

    $sqldebug = '';
    $n = 0;
    $sqlw = array();
    $db = & DB::object();
    $queries = count($db->sqldebug);
    foreach ($db->sqldebug as $string) {
        $n++;
        $sqldebug .= '<li><span style="cursor:pointer" onclick="document.getElementById(\'sql_'.$n.'\').style.display = document.getElementById(\'sql_'.$n.'\').style.display == \'\' ? \'none\' : \'\'">'.$string[1].'s &bull; '.nl2br(htmlspecialchars($string[0])).'</span<br /></li>';
        $sqldebug .= '<div id="sql_'.$n.'" style="display:none">';
        if(preg_match('/^SELECT /', $string[0])) {
            $query = DB::query("EXPLAIN ".$string[0]);
            $i = 0;
            $sqldebug .= '<table style="border-bottom:none">';
            while($row = DB::fetch($query)) {
                if(!$i) {
                    $sqldebug .= '<tr style="border-bottom:1px dotted gray"><td>&nbsp;'.implode('&nbsp;</td><td>&nbsp;', array_keys($row)).'&nbsp;</td></tr>';
                    $i++;
                }
                if(strexists($row['Extra'], 'Using filesort')) {
                    $sqlw['Using filesort']++;
                    $row['Extra'] = str_replace('Using filesort', '<font color=red>Using filesort</font>', $row['Extra']);
                }
                if(strexists($row['Extra'], 'Using temporary')) {
                    $sqlw['Using temporary']++;
                    $row['Extra'] = str_replace('Using temporary', '<font color=red>Using temporary</font>', $row['Extra']);
                }
                $sqldebug .= '<tr><td>&nbsp;'.implode('&nbsp;</td><td>&nbsp;', $row).'&nbsp;</td></tr>';
            }
            $sqldebug .= '</table>';
        }
        $sqldebug .= '<table><tr style="border-bottom:1px dotted gray"><td width="270">File</td><td width="80">Line</td><td>Function</td></tr>';
        foreach($string[2] as $error) {
            $error['file'] = str_replace(DISCUZ_ROOT, '', $error['file']);
            $error['class'] = isset($error['class']) ? $error['class'] : '';
            $error['type'] = isset($error['type']) ? $error['type'] : '';
            $error['function'] = isset($error['function']) ? $error['function'] : '';
            $sqldebug .= "<tr><td>$error[file]</td><td>$error[line]</td><td>$error[class]$error[type]$error[function]()</td></tr>";
        }
        $sqldebug .= '</table></div>';
    }
    $ajaxhtml = 'data/'.$debugfile.'_ajax.php';
    if($ajax) {
        $idk = substr(md5($_SERVER['SCRIPT_NAME'].'?'.$_SERVER['QUERY_STRING']), 0, 4);
        $sqldebug = '<b style="cursor:pointer" onclick="document.getElementById(\''.$idk.'\').style.display=document.getElementById(\''.$idk.'\').style.display == \'\' ? \'none\' : \'\'">Queries: </b> '.$queries.' ('.$_SERVER['SCRIPT_NAME'].'?'.$_SERVER['QUERY_STRING'].')<ol id="'.$idk.'" style="display:none">'.$sqldebug.'</ol><br>';
        file_put_contents(DISCUZ_ROOT.'./'.$ajaxhtml, $sqldebug, FILE_APPEND);
        return;
    }
    file_put_contents(DISCUZ_ROOT.'./'.$ajaxhtml, '<?php if(empty($_GET[\'k\']) || $_GET[\'k\'] != \''.$akey.'\') { exit; } ?><style>body,table { font-size:12px; }table { width:90%;border:1px solid gray; }</style><a href="javascript:;" onclick="location.href=location.href">Refresh</a><br />');
    foreach($sqlw as $k => $v) {
        $sqlw[$k] = $k.': '.$v;
    }
    $sqlw = $sqlw ? '<s>('.implode(', ', $sqlw).')</s>' : '';

    $debug = '<?php if(empty($_GET[\'k\']) || $_GET[\'k\'] != \''.$akey.'\') { exit; } ?>';
    if($_G['adminid'] == 1 && !$ajax) {
        $debug .= '<?php
if(isset($_GET[\''.$phpinfok.'\'])) { phpinfo(); exit; }
elseif(isset($_GET[\''.$viewcachek.'\'])) {
	chdir(\'../\');
	require \'./source/class/class_core.php\';
	$discuz = & discuz_core::instance();
	$discuz->init();
	echo \'<style>body { font-size:12px; }</style>\';
	if(!isset($_GET[\'c\'])) {
		$query = DB::query("SELECT cname FROM ".DB::table("common_syscache"));
		while($names = DB::fetch($query)) {
			echo \'<a href="'.$debugfile.'?k='.$akey.'&'.$viewcachek.'&c=\'.$names[\'cname\'].\'" target="_blank" style="float:left;width:200px">\'.$names[\'cname\'].\'</a>\';
		}
	} else {
		loadcache($_GET[\'c\']);
		echo \'$_G[\\\'cache\\\'][\'.$_GET[\'c\'].\']<br>\';
		debug($_G[\'cache\'][$_GET[\'c\']]);
	}
	exit;
}
elseif(isset($_GET[\''.$mysqlplek.'\'])) {
	chdir(\'../\');
	require \'./source/class/class_core.php\';
	$discuz = & discuz_core::instance();
	$discuz->_init_db();
	if(!empty($_GET[\'Id\'])) {
		$query = DB::query("KILL ".floatval($_GET[\'Id\']), \'SILENT\');
	}
	$query = DB::query("SHOW FULL PROCESSLIST");
	echo \'<style>table { font-size:12px; }</style>\';
	echo \'<table style="border-bottom:none">\';
	while($row = DB::fetch($query)) {
		if(!$i) {
			echo \'<tr style="border-bottom:1px dotted gray"><td>&nbsp;</td><td>&nbsp;\'.implode(\'&nbsp;</td><td>&nbsp;\', array_keys($row)).\'&nbsp;</td></tr>\';
			$i++;
		}
		echo \'<tr><td><a href="'.$debugfile.'?k='.$akey.'&P&Id=\'.$row[\'Id\'].\'">[Kill]</a></td><td>&nbsp;\'.implode(\'&nbsp;</td><td>&nbsp;\', $row).\'&nbsp;</td></tr>\';
	}
	echo \'</table>\';
	exit;
}
		?>';
    }
    $debug .= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><head>';
    $debug .= "<script src='../static/js/common.js?".VERHASH."'></script><script>
	function switchTab(prefix, current, total, activeclass) {
	activeclass = !activeclass ? 'a' : activeclass;
	for(var i = 1; i <= total;i++) {
		var classname = ' '+$(prefix + '_' + i).className+' ';
		$(prefix + '_' + i).className = classname.replace(' '+activeclass+' ','').substr(1);
		$(prefix + '_c_' + i).style.display = 'none';
	}
	$(prefix + '_' + current).className = $(prefix + '_' + current).className + ' '+activeclass;
	$(prefix + '_c_' + current).style.display = '';
	}
	</script>";

    if(!defined('IN_ADMINCP') && file_exists(DISCUZ_ROOT.'./static/image/common/temp-grid.png')) $debug .= <<<EOF
<script type="text/javascript">
var s = '<button style="position: fixed; width: 40px; right: 0; top: 30px; border: none; border:1px solid orange;background: yellow; color: red; cursor: pointer;" onclick="var pageHight = top.document.body.clientHeight;$(\'tempgrid\').style.height = pageHight + \'px\';$(\'tempgrid\').style.visibility = top.$(\'tempgrid\').style.visibility == \'hidden\'?\'\':\'hidden\';o.innerHTML = o.innerHTML == \'网格\'?\'关闭\':\'网格\';">网格</button>';
s += '<div id="tempgrid" style="position: absolute; top: 0px; left: 50%; margin-left: -500px; width: 1000px; height: 0; background: url(static/image/common/temp-grid.png); visibility :hidden;"></div>';
top.$('_debug_div').innerHTML = s;
</script>
EOF;

    $_GS = $_GA = '';
    if($_G['adminid'] == 1) {
        foreach($_G as $k => $v) {
            if(is_array($v)) {
                if($k != 'lang') {
                    $_GA .= "<li><a name=\"S_$k\"></a><br />['$k'] => ".nl2br(str_replace('  ','&nbsp;', htmlspecialchars(print_r($v, true)))).'</li>';
                }
            } elseif(is_object($v)) {
                $_GA .= "<li><br />['$k'] => <i>object of ".get_class($v)."</i></li>";
            } else {
                $_GS .= "<li><br />['$k'] => ".htmlspecialchars($v)."</li>";
            }
        }
    }
    $modid = $_G['basescript'].(!defined('IN_ADMINCP') ? '::'.CURMODULE : '');
    $svn = '';
    if(file_exists(DISCUZ_ROOT.'./.svn/entries')) {
        $svn = @file(DISCUZ_ROOT.'./.svn/entries');
        $time = $svn[9];
        preg_match('/([\d\-]+)T([\d:]+)/', $time, $a);
        $svn = '<em>SVN 版本:</em> '.$svn[10].' (最后由 '.$svn[11].' 于 '.dgmdate(strtotime($a[1].' '.$a[2]) + $_G['setting']['timeoffset'] * 3600).' 提交)<br />';
    }
    $max = $_G['adminid'] == 1 ? 6 : 5;
    $debug .= '
		<style>#__debugbarwrap__ { line-height:10px; text-align:left;font:12px Monaco,Consolas,"Lucida Console","Courier New",serif;}
		body { font-size:12px; }
		a, a:hover { color: black;text-decoration:none; }
		s { text-decoration:none;color: red; }
		img { vertical-align:middle; }
		.w td em { margin-left:10px;font-style: normal; }
		#__debugbar__ { padding: 80px 1px 0 1px;  }
		#__debugbar__ table { width:90%;border:1px solid gray; }
		#__debugbar_s { border-bottom:1px dotted #EFEFEF;background:#FFF;width:100%;font-size:11px;position: fixed; top:0px; left:5px; }
		#__debugbar_s a { color:blue; }
		#__debugbar_s a.a { border-bottom: 1px dotted gray; }
		#__debug_c_1 ol { margin-left: 20px; padding: 0px; }
		#__debug_c_4_nav { background:#FFF; border:1px solid black; border-top:none; padding:5px; position: fixed; top:0px; right:0px }
		</style></head><body>'.
        '<div id="__debugbarwrap__">'.
        '<div id="__debugbar_s">
			<table class="w" width=99%><tr><td valign=top width=50%>'.
        '<em>发行版本:</em> Discuz! '.DISCUZ_VERSION.' '.DISCUZ_RELEASE.'<br />'.$svn.
        '<em>服务端:</em> '.PHP_OS.', '.$_SERVER['SERVER_SOFTWARE'].' MySQL/'.DB::result_first("SELECT VERSION()").'<br />'.
        '<em>客户端:</em> <a id="__debug_2" href="#debugbar" onclick="parent.$(\'_debug_iframe\').height=\'300px\';switchTab(\'__debug\', 2, '.$max.')">[详情]</a> <span id="__debug_b"></span>'.
        '<td valign=top>'.
        $m.
        '<em>SQL 查询:</em> '.
        '<a id="__debug_1" href="#debugbar" onclick="parent.$(\'_debug_iframe\').height=\'800px\';switchTab(\'__debug\', 1, '.$max.')">[SQL列表]</a>'.
        '<a id="__debug_4" href="#debugbar" onclick="parent.$(\'_debug_iframe\').height=\'800px\';switchTab(\'__debug\', 4, '.$max.');sqldebug_ajax.location.href = sqldebug_ajax.location.href;">[AjaxSQL列表]</a>'.
        ' <s>'.$queries.$sqlw.($_G['debuginfo']['time'] ? ' in '.$_G['debuginfo']['time'].'s' : '').'</s><br />'.
        '<em>包含文件:</em> '.
        '<a id="__debug_3" href="#debugbar" onclick="parent.$(\'_debug_iframe\').height=\'500px\';switchTab(\'__debug\', 3, '.$max.')">[文件列表]</a>'.
        ' <s>'.(count($includes) - 1).($_G['debuginfo']['time'] ? ' in '.number_format(($finaltime - $_G['debuginfo']['time']), 6).'s' : '').'</s><br />'.
        '<em>页面 ModID:</em> <s>'.$modid.'</s><br />'.
        '<tr><td colspan=2><a name="debugbar">&nbsp;</a>'.
        '<a href="javascript:;" onclick="parent.scrollTo(0,0)" style="float:right">[TOP]&nbsp;&nbsp;&nbsp;</a>'.
        '<img src="../static/image/common/arw_r.gif" /><a id="__debug_5" href="#debugbar" onclick="parent.$(\'_debug_iframe\').height=\'300px\';switchTab(\'__debug\', 5, '.$max.')">$_COOKIE</a>'.
        ($_G['adminid'] == 1 ? '<img src="../static/image/common/arw_r.gif" /><a id="__debug_6" href="#debugbar" onclick="parent.$(\'_debug_iframe\').height=\'1000px\';switchTab(\'__debug\', 6, 6)">$_G</a>' : '').
        ($_G['adminid'] == 1 ?
            '<img src="../static/image/common/arw_r.gif" /><a href="'.$debugfile.'?k='.$akey.'&'.$phpinfok.'" target="_blank">phpinfo()</a>'.
            '<img src="../static/image/common/arw_r.gif" /><a href="'.$debugfile.'?k='.$akey.'&'.$mysqlplek.'" target="_blank">MySQL 进程列表</a>'.
            '<img src="../static/image/common/arw_r.gif" /><a href="'.$debugfile.'?k='.$akey.'&'.$viewcachek.'" target="_blank">查看缓存</a>'.
            '<img src="../static/image/common/arw_r.gif" /><a href="../misc.php?mod=initsys" target="_debug_initframe" onclick="parent.$(\'_debug_initframe\').onload = function () {parent.location.href=parent.location.href;}">更新缓存</a>' : '').
        '<img src="../static/image/common/arw_r.gif" /><a href="../install/update.php" target="_blank">执行 update.php</a>'.
        '</table>'.
        '</div>'.
        '<div id="__debugbar__" style="clear:both">'.
        '<div id="__debug_c_1" style="display:none"><b>Queries: </b> '.$queries.'<ol>';
    $debug .= $sqldebug.'';
    $debug .= '</ol></div>'.
        '<div id="__debug_c_4" style="display:none"><iframe id="sqldebug_ajax" name="sqldebug_ajax" src="../'.$ajaxhtml.'?k='.$akey.'" frameborder="0" width="100%" height="800"></iframe></div>'.
        '<div id="__debug_c_2" style="display:none"><b>IP: </b>'.$_G['clientip'].'<br /><b>User Agent: </b>'.$_SERVER['HTTP_USER_AGENT'].'<br /><b>BROWSER.x: </b><script>for(BROWSERi in BROWSER) {var __s=BROWSERi+\':\'+BROWSER[BROWSERi]+\' \';$(\'__debug_b\').innerHTML+=BROWSER[BROWSERi]!==0?__s:\'\';document.write(__s);}</script></div>'.
        '<div id="__debug_c_3" style="display:none"><ol>';
    foreach ($includes as $fn) {
        $fn = str_replace(array(DISCUZ_ROOT, "\\"), array('', '/'), $fn);
        $debug .= '<li>';
        if(preg_match('/^source\/plugin/', $fn)) {
            $debug .= '[插件]';
        } elseif(preg_match('/^source\//', $fn)) {
            $debug .= '[脚本]';
        } elseif(preg_match('/^data\/template\//', $fn)) {
            $debug .= '[模板]';
        } elseif(preg_match('/^data/', $fn)) {
            $debug .= '[缓存]';
        } elseif(preg_match('/^config/', $fn)) {
            $debug .= '[配置]';
        }
        $debug .= $fn.'</li>';
    }
    $debug .= '<ol></div><div id="__debug_c_5" style="display:none"><ol>';
    foreach($_COOKIE as $k => $v) {
        if(strexists($k, $_G['config']['cookie']['cookiepre'])) {
            $k = '<font color=blue>'.$k.'</font>';
        }
        $debug .= "<li><br />['$k'] => ".htmlspecialchars($v)."</li>";
    }
    $debug .= '</ol></div><div id="__debug_c_6" style="display:none">'.
        '<div id="__debug_c_4_nav"><a href="#S_config">Nav:<br />
			<a href="#top">#top</a><br />
			<a href="#S_config">$_G[\'config\']</a><br />
			<a href="#S_setting">$_G[\'setting\']</a><br />
			<a href="#S_member">$_G[\'member\']</a><br />
			<a href="#S_group">$_G[\'group\']</a><br />
			<a href="#S_cookie">$_G[\'cookie\']</a><br />
			<a href="#S_style">$_G[\'style\']</a><br />
			<a href="#S_cache">$_G[\'cache\']</a><br />
			</div>'.
        '<ol><a name="top"></a>'.$_GS.$_GA.'</ol></div></body></html>';
    $fn = 'data/'.$debugfile;
    file_put_contents(DISCUZ_ROOT.'./'.$fn, $debug);
    echo '<iframe src="'.$fn.'?k='.$akey.'" name="_debug_iframe" id="_debug_iframe" style="border-top:1px solid gray;overflow-x:hidden;overflow-y:auto" width="100%" height="100" frameborder="0"></iframe><div id="_debug_div"></div><iframe name="_debug_initframe" id="_debug_initframe" style="display:none"></iframe>';
}

?>
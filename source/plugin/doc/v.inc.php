<?php

/**
 *   @CopyRight   http://www.docswf.com
 *   @author      QQ50245077
 */
if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
require './source/plugin/doc/function.php';
global $_G;
$doc = $_G['cache']['plugin']['doc'];
$doc_viewgroups = unserialize($doc['doc_viewgroups']);
$doc_vipgroups = unserialize($doc['doc_vipgroups']);

$doc_oss = $doc['doc_oss'];
if ($doc_oss) {
    require './source/plugin/doc/Oss/Oss.php';
}

$loginuserid = $_G['uid'];
$docid = $_GET['d'] == null ? 0 : $_GET['d'];
$cpage = $_GET['p'] == null ? 1 : $_GET['p'];
$ish5 = $_GET['ish5'] == null ? 0 : $_GET['ish5'];

if (!in_array($_G[groupid], $doc_viewgroups)) {
    if (!$_G['uid']) {
        showmessage(lang('plugin/doc', 'pleaselogin'), '', array(), array('login' => true));
        exit(0);
    } else {
        showmessage(lang('plugin/doc', 'norightview'), 'plugin.php?id=doc:doc');
        exit(0);
    }
}
if ($docid != -1 && $cpage == 'fonts') {
    $DocInfo = GetDocInfo($docid);
    if ($DocInfo) {
        $docdir = $DocInfo[2];
        header('Content-type:text/css');
        readfile($docdir . 'fonts');
    } else {
        echo 1;
    }
} else if ($docid != -1 && $cpage != -1) {
    $DocInfo = GetDocInfo($docid);
    $docdir = $DocInfo[2];
    $freepage = (int) $DocInfo[9];
    $userid = $DocInfo[12];
    $pcount = $DocInfo[18];
    if ($freepage <= -1 || $freepage >= $pcount || $freepage === NULL || IsPayRead($docid, $loginuserid) || $loginuserid == $userid) {
        $freepage = $pcount;
    }
    if (in_array($_G[groupid], $doc_vipgroups)) {
        $freepage = $pcount;
    }
    ob_end_clean();
    
    $thum = $docdir . '1.png';

    if (!file_exists($thum) && $doc_oss && ($oss = new Oss()) && $oss->objectexists($thum)) {
        $url = $oss->signUrl($thum);
        file_put_contents($thum, file_get_contents($url));
    }

    if (!file_exists($thum)) {
        if ($ish5)
            readfile('source/plugin/doc/js/docread.png/4.htm' . ($_G['charset'] == 'gbk' ? '' : 'l'));
        else
            readfile('source/plugin/doc/js/docread.png/4.png');
        exit();
    }

    if ($cpage > $freepage) {
        if ($ish5)
            readfile('source/plugin/doc/js/docread.png/4.htm' . ( $_G['charset'] == 'gbk' ? '' : 'l'));
        else
            readfile('source/plugin/doc/js/docread.png/4.png');
        exit();
    }

    $size = filesize($thum);
    $fp = @fopen($thum, "rb");
    if ($fp) {
        fseek($fp, $size - 5);
        $a1 = '';
        $a2 = '';
        $v = bin2hex(fread($fp, 1));
        for ($i = 0; $i < 4; $i++) {
            $a1 = fread($fp, 1);
            $a2 = bin2hex($a1) . $a2;
        }
    } else {
        if ($ish5)
            readfile('source/plugin/doc/js/docread.png/4.htm' . ( $_G['charset'] == 'gbk' ? '' : 'l'));
        else
            readfile('source/plugin/doc/js/docread.png/4.png');
        return;
    }
    fclose($fp);
    $id = base_convert($a2, 16, 10);
    if ($v . $id) {
        if ($v == '7f') {
            $passf = 'source/plugin/doc/js/pass';
            $fpp = @fopen($passf, "rb");
            if ($fpp) {
                $p2 = '';
                for ($i = 0; $i < 32; $i++) {
                    $p1 = fread($fpp, 1);
                    $p2 = $p2 . ',' . base_convert(bin2hex($p1), 16, 10);
                }
                $pass = array('2', substr($p2, 1));
            } else {
                if ($ish5)
                    readfile('source/plugin/doc/js/docread.png/4.htm' . ( $_G['charset'] == 'gbk' ? '' : 'l'));
                else
                    readfile('source/plugin/doc/js/docread.png/4.png');
                return;
            }
        } else {
            $pass = explode("|", CSee($v . $id));
        }

        if ($pass[0] == "1" || $pass[0] == "2") {
            $file = $docdir . $cpage . ($ish5 ? '.h5' : ".view");

            if (!file_exists($file) && $doc_oss && ($oss = new Oss()) && $oss->objectexists($file)) {
                $url = $oss->signUrl($file);
                file_put_contents($file, file_get_contents($url));
            }

            if (!file_exists($file)) {
                if ($ish5)
                    readfile('source/plugin/doc/js/docread.png/4.htm' . ($_G['charset'] == 'gbk' ? '' : 'l'));
                else
                    readfile('source/plugin/doc/js/docread.png/4.png');
                exit();
            }
            $fp = fopen($file, "rb");
            if ($fp) {
                $pass = explode(",", $pass[1]);
                $b2 = '';
                for ($i = 0; $i < 4; $i++) {
                    $b1 = fread($fp, 1);
                    $b2 = bin2hex($b1) . $b2;
                }
                $buf_length = base_convert($b2, 16, 10);

                $data = fread($fp, $buf_length);
                fclose($fp);

                $sp_size = floor($buf_length / 32);
                $p_extra = $buf_length - $sp_size * 32;
                $data_dec = '';
                for ($i = 0; $i < $sp_size; $i++) {
                    foreach ($pass as $p) {
                        $data_dec .= $data[$i * 32 + $p];
                    }
                }
                if ($p_extra > 0)
                    $data_dec .= substr($data, -$p_extra);
				
				if (strpos($data_dec, 'xml') === 5 || strpos($data_dec, 'xml') === 2) {
					header('Content-type: image/svg+xml');
				} else {
					!$ish5 && header('Content-type: image/png');
				}
				
                if ($ish5)
                    echo $_G['charset'] == 'gbk' ? iconv('utf-8', 'gbk//IGNORE', $data_dec) : $data_dec;
                else
                    echo $data_dec;
            } else {
                if ($ish5)
                    readfile('source/plugin/doc/js/docread.png/4.htm' . ($_G['charset'] == 'gbk' ? '' : 'l'));
                else
                    readfile('source/plugin/doc/js/docread.png/4.png');
            }
        } else if ($pass[0] == "-1") {
            if ($ish5)
                readfile('source/plugin/doc/js/docread.png/-1.htm' . ($_G['charset'] == 'gbk' ? '' : 'l'));
            else
                readfile('source/plugin/doc/js/docread.png/-1.png');
        } else if ($pass[0] == "-2") {
            if ($ish5)
                readfile('source/plugin/doc/js/docread.png/-2.htm' . ( $_G['charset'] == 'gbk' ? '' : 'l'));
            else
                readfile('source/plugin/doc/js/docread.png/-2.png');
        } else {
            if ($ish5)
                readfile('source/plugin/doc/js/docread.png/-4.htm' . ( $_G['charset'] == 'gbk' ? '' : 'l'));
            else
                readfile('source/plugin/doc/js/docread.png/-4.png');
        }
    } else {
        if ($ish5)
            readfile('source/plugin/doc/js/docread.png/-4.htm' . ($_G['charset'] == 'gbk' ? '' : 'l'));
        else
            readfile('source/plugin/doc/js/docread.png/4.png');
    }
} else {
    if ($ish5)
        readfile('source/plugin/doc/js/docread.png/-4.htm' . ($_G['charset'] == 'gbk' ? '' : 'l'));
    else
        readfile('source/plugin/doc/js/docread.png/4.png');
}

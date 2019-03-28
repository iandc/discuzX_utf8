<?php
error_reporting(E_ERROR);
ob_start();
header("HTTP/1.1 301 Moved Permanently");

$tid = isset($_GET['tid']) ? intval($_GET['tid']) : 0;
$pid = isset($_GET['pid']) ? intval($_GET['pid']) : 0;
$ptid = isset($_GET['ptid']) ? intval($_GET['ptid']) : 0;
$goto = isset($_GET['goto']) ? $_GET['goto'] : '';

if($tid) {
    $url = 'forum.php?mod=viewthread&tid='.$tid;
    if(is_numeric($_GET['page'])) {
        $url .= '&page='.$_GET['page'];
    }
} else {
    $url = 'forum.php?mod=redirect&goto='."$goto&ptid=$ptid&pid=$pid";
}

header("location: $url");
?>

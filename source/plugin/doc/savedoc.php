<?php

/**
 *   @CopyRight   http://www.docswf.com
 *   @author      QQ50245077
 */
if (isset($_POST["PHPSESSID"])) {
    session_id($_POST["PHPSESSID"]);
} else if (isset($_GET["PHPSESSID"])) {
    session_id($_GET["PHPSESSID"]);
}
session_start();

date_default_timezone_set('Asia/Shanghai');

$POST_MAX_SIZE = ini_get('post_max_size');
$unit = strtoupper(substr($POST_MAX_SIZE, -1));
$multiplier = ($unit == 'M' ? 1048576 : ($unit == 'K' ? 1024 : ($unit == 'G' ? 1073741824 : 1)));

if ((int) $_SERVER['CONTENT_LENGTH'] > $multiplier * (int) $POST_MAX_SIZE && $POST_MAX_SIZE) {
    header("HTTP/1.1 500 Internal Server Error");
    echo "POST exceeded maximum allowed size.";
    exit(0);
}
$path_info = pathinfo($_FILES["Filedata"]['name']);
$file_extension = $path_info["extension"];
$extension_whitelist = array("doc", "docx", "ppt", "pptx", "xls", "xlsx", "pdf", "txt", "wps", "dps", "et");
$is_valid_extension = false;
foreach ($extension_whitelist as $extension) {
    if (strcasecmp($file_extension, $extension) == 0) {
        $is_valid_extension = true;
        break;
    }
}
if (!$is_valid_extension) {
    echo "Invalid file extension";
    exit(0);
}

$doc_dir = 'data/';
$ddyy_dir = GetYDDir($doc_dir);
$file_name = GetDocName();
$file_path = $doc_dir . $ddyy_dir . $file_name . '.' . $file_extension;

if (@move_uploaded_file($_FILES["Filedata"]["tmp_name"], $file_path)) {
    if (!is_dir($doc_dir . $ddyy_dir . $file_name)) {
        @mkdir($doc_dir . $ddyy_dir . $file_name, 0777);
    }
    echo $file_path;
} else {
    echo "File could not be saved.";
    exit(0);
}

function GetYDDir($path) {
    $yy = date('Ym') . '/';
    $dd = date('d') . '/';
    $subdir0 = $path;
    $subdir1 = $subdir0 . $yy;
    $subdir2 = $subdir1 . $dd;
    if (!is_dir($subdir0)) {
        @mkdir($subdir0, 0777);
    }
    if (!is_dir($subdir1)) {
        @mkdir($subdir1, 0777);
    }
    if (!is_dir($subdir2)) {
        @mkdir($subdir2, 0777);
    }
    return $yy . $dd;
}

function GetDocName() {
    list($tmp1, $tmp2) = explode(' ', microtime());
    return sprintf('%.0f', (floatval($tmp1) + floatval($tmp2)) * 1000);
}

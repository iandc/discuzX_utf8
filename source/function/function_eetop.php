<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function getCreditByAttachSize($attachSize)
{
    $credit = 0;
    if ($attachSize > 32 * 1048576) {
        $credit = 11;
    } else if ($attachSize > 28 * 1048576 && $attachSize <= 32 * 1048576) {
        $credit = 8;
    } else if ($attachSize > 24 * 1048576 && $attachSize <= 28 * 1048576) {
        $credit = 7;
    } else if ($attachSize > 20 * 1048576 && $attachSize <= 24 * 1048576) {
        $credit = 6;
    } else if ($attachSize > 16 * 1048576 && $attachSize <= 20 * 1048576) {
        $credit = 5;
    } else if ($attachSize > 12 * 1048576 && $attachSize <= 16 * 1048576) {
        $credit = 4;
    } else if ($attachSize > 8 * 1048576 && $attachSize <= 12 * 1048576) {
        $credit = 3;
    } else if ($attachSize > 4 * 1048576 && $attachSize <= 8 * 1048576) {
        $credit = 2;
    } else if ($attachSize > 0 * 1048576 && $attachSize <= 4 * 1048576) {
        $credit = 1;
    }
    return $credit;
}

function addEeTopCredits($matches) {
    return ($matches[1]) . $matches[2] . ($matches[3]);
}

function preg_replace_url($subject)
{
    $linkPattern = '/href=[\'|\"](\S+)[\'|\"]/i';
    $callback = function ($matches) {
        return 'href="'.urldecode($matches[1]) . '"';
    };
    return preg_replace_callback($linkPattern, $callback, $subject);
}

?>
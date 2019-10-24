<?php

/*
 * 正在绑定手机号，您的验证码是#code#。如非本人操作，请忽略本短信
 * 正在注册ET芯学堂，您的验证码是#code#。如非本人操作，请忽略本短信
 * 正在修改手机号，您的验证码是#code#。如非本人操作，请忽略本短信
 * 正在找回密码，您的验证码是#code#。如非本人操作，请忽略本短信
 */

function sendsms($mobiles, $msgText)
{
    global $_G;
    global $smsportlog;

    $prefix = '【ET芯学堂】';
    $mobiles = '13264360742';
    $msgText = '正在注册ET芯学堂，您的验证码是222222。如非本人操作，请忽略本短信';

    if (!$mobiles) {
        writelog('smslog', 'mobiles was error');
        return -5;
    }
    if (!$msgText) {
        writelog('smslog', 'msgText was error');
        return -7;
    }
    if (isset($_G['setting']['qxt_login_setting'])) {
        $smsset = unserialize($_G['setting']['qxt_login_setting']);
    } else {
        writelog('smslog', 'qxt_login_setting was error');
        //return -6;
    }

    $apiUrl = 'https://sms.100sms.cn/api/sms/batchSubmit';
    $apiKey = '255ffc031f3940fab4bdd4e8000d492e';

    require_once('YibaiSdk.php');

    $client = new YibaiClient($apiUrl, $apiKey);

    try {
        $response = $client->smsBatchSubmit(array(
            new SmsSubmit($mobiles, $prefix.$msgText),
        ));
        writelog('smslog', $response);
    } catch (YibaiApiException $e) {
        writelog('smslog', 'YibaiApiException, code: ' . $e->getCode() . ', message: ' . $e->getMessage());
    } catch (Exception $e) {
        writelog('smslog', 'Exception. message: ' . $e->getMessage());
    }

    return 1;
}

function sendsmsBak($mobiles, $msgText)
{
    global $_G;
    global $smsportlog;
    if (!$mobiles) {
        return -5;
    }
    if (!$msgText) {
        return -7;
    }
    if (isset($_G['setting']['qxt_login_setting'])) {
        $smsset = unserialize($_G['setting']['qxt_login_setting']);
    } else {
        return -6;
    }

    $p_sendaction = "uid=$smsset[smsuid]&username=$smsset[smsname]&token=$smsset[token]&appid=$smsset[appid]&content=$msgText&mobile=$mobiles";
    if (CHARSET != "gbk") {
        $p_sendaction = diconv($p_sendaction, CHARSET, "gbk");
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.91qxt.com/api/sms/index.php");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $p_sendaction);
    $smsportlog = curl_exec($ch);
    curl_close($ch);

    $strpos = strpos($smsportlog, "success");
    if ($strpos !== false) {
        return 1;
    } else {
        return 2;
    }
}

function smsportnum($url, $queryaction, $type)
{
    $smsnum = "null";
    if ($type == "post") {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $queryaction);
        $smsnum = curl_exec($ch);
        curl_close($ch);
    } elseif ($type == "get") {
        $url = $url . "?" . $queryaction;
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $smsnum = curl_exec($ch);
        curl_close($ch);
    }
    return $smsnum;
}

?>
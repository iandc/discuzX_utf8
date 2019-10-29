<?php

/*
 * 正在绑定手机号，您的验证码是#code#。如非本人操作，请忽略本短信
 * 正在注册ET芯学堂，您的验证码是#code#。如非本人操作，请忽略本短信
 * 正在修改手机号，您的验证码是#code#。如非本人操作，请忽略本短信
 * 正在找回密码，您的验证码是#code#。如非本人操作，请忽略本短信
 */

$debug = 0;

function sendsms($mobiles, $msgText)
{
    global $_G;
    global $debug;

    $debug == 1 && writelog('smslog', "debug info:mobiles=$mobiles,msgText=$msgText");

    if (!$mobiles) {
        return -5;
    }
    if (!$msgText) {
        return -7;
    }
    if (isset($_G['setting']['qxt_login_setting'])) {
        $smsset = unserialize($_G['setting']['qxt_login_setting']);
    } else {
        $debug == 1 && writelog('smslog', 'qxt_login_setting was error');
        return -6;
    }

    $debug == 1 && writelog('smslog', "debug info:smsset=" . json_encode($smsset));

    $url = $smsset['url'];
    $key = $smsset['key'];

    $response = postJson($url, json_encode(array(
        'apikey' => $key,
        'submits' => array(
            array(
                'mobile' => $mobiles,
                'message' => $msgText
            )
        )
    )));

    $debug == 1 && writelog('smslog', "debug info:response=" . $response);

    $response = json_decode($response, true);
    if ($response['response'][0]['code'] == 200) {
        return 1;
    } else {
        return 2;
    }
}

function postJson($url, $json, $timeout = 5)
{
    $ch = curl_init($url);
    curl_setopt_array($ch, array(
        CURLOPT_POST => TRUE,
        CURLOPT_RETURNTRANSFER => TRUE,
        CURLOPT_SSL_VERIFYPEER => FALSE,
        CURLOPT_HTTPHEADER => array(
            'Content-Type:application/json;charset=utf-8'
        ),
        CURLOPT_POSTFIELDS => $json,
        CURLOPT_CONNECTTIMEOUT => $timeout
    ));

    $output = curl_exec($ch);

    if (curl_errno($ch) !== 0) { // 请求失败
        $curlError = curl_error($ch);
        curl_close($ch);
        throw new Exception('Http request error. ' . $curlError);
    }
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($statusCode !== 200) {
        curl_close($ch);
        throw new Exception('Http request error. Status code: ' . $statusCode);
    }
    curl_close($ch);
    return $output;
}


function sendsmsbak1($mobiles, $msgText)
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
        //writelog('smslog', 'qxt_login_setting was error');
        //return -6;
    }

    $apiUrl = 'https://sms.100sms.cn/api/sms/batchSubmit';
    $apiKey = '255ffc031f3940fab4bdd4e8000d492e';

    require_once('YibaiSdk.php');

    $client = new YibaiClient($apiUrl, $apiKey);

    try {
        $response = $client->smsBatchSubmit(array(
            new SmsSubmit($mobiles, $prefix . $msgText),
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
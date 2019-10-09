<?php
/**
* 说明:
* 以下代码展示的是非sdk下的调用，只是为了方便用户测试而提供的样例代码，用户也可自行编写。
* 正式环境建议使用sdk进行调用以提高效率，sdk中包含了使用样例
*/
$response = postJson('https://xxxxxxxxxxxxx/api/sms/batchSubmit', json_encode(array(
    'apikey' => 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx', // 修改为您的apikey
    'submits' => array(
        array(
            'mobile' => '186xxxxxxxx', // 修改为您要发送的手机号
            'message' => '【亿佰云通讯】您的验证码是：123456' // 修改为您要发送的内容，内容必须和某个模板匹配
        )
    )
)));
print_r($response);

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

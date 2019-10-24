<?php
/**
* 说明:
* 以下代码展示的是非sdk下的调用，只是为了方便用户测试而提供的样例代码，用户也可自行编写。
* 正式环境建议使用sdk进行调用以提高效率，sdk中包含了使用样例
*/
$response = postJson('https://sms.100sms.cn/api/sms/batchSubmit', json_encode(array(
    'apikey' => '255ffc031f3940fab4bdd4e8000d492e',
    'submits' => array(
        array(
            'mobile' => '13264360742',
            'message' => '【ET芯学堂】正在注册ET芯学堂，您的验证码是123456。如非本人操作，请忽略本短信'
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

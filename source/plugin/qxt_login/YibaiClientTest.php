<?php

require_once('YibaiSdk.php');

$apiUrl = 'https://api.100sms.cn/api/sms/batchSubmit';
$apiKey = '255ffc031f3940fab4bdd4e8000d492e';

$client = new YibaiClient($apiUrl, $apiKey);

try {
    $response = $client->smsBatchSubmit(array(
        new SmsSubmit('186xxxxxxxx', '【亿佰云通讯】您的验证码是：111111'),
        new SmsSubmit('187xxxxxxxx', '【亿佰云通讯】您的验证码是：222222')
    ));
    print_r($response);
} catch (YibaiApiException $e) {
    print_r('YibaiApiException, code: ' . $e->getCode() . ', message: '. $e->getMessage());
} catch (Exception $e) {
    print_r('Exception. message: ' . $e->getMessage());
}

<?php
/*
   判断一个ip是否国内的ip
   需要连接到redis服务器进行判断
   by 刘宏缔
   2020.04.01
 */

define("REDIS_SERVER", "127.0.0.1");
define("REDIS_PORT", "6379");
define("IP_HASH_NAME", "china_ip_hash");

/*$ip = "203.137.164.152";
$ip = $argv[1];
$is_in = is_ip_in_china($ip);
if ($is_in == 1) {
    echo "in china,ret=$is_in\n";
} else {
    echo "out china,ret=$is_in\n";
}*/


//------------------------------------------------function

//判断一个ip是否属于china
function is_ip_in_china($ip) {
    $ip = trim($ip);
    $first_a = explode(".", $ip);
    if (!isset($first_a[0]) || $first_a[0] == "") {
        //ip有误，按国外算
        return -1;
    }
    $first = $first_a[0];

    $arr_range = hash_get(IP_HASH_NAME,$first);
    if (!is_array($arr_range)) {
        return -2;
    }

    if(sizeof($arr_range) == 0) {
        return -3;
    }

    if (is_ip_in_arr_range($ip,$arr_range) == true) {
        return 1;
    } else {
        return -4;
    }
}

//判断一个ip是否属于ip的range数组
function is_ip_in_arr_range($ip,$arr_range) {
    $ip_long = (double) (sprintf("%u", ip2long($ip)));
    foreach ($arr_range as $k => $one) {
        $one = trim($one);
        //echo $one.":\n";
        $arr_one = explode("--", $one);

        if (!isset($arr_one[0]) || !isset($arr_one[1])) {
            continue;
        }

        $begin = $arr_one[0];
        $end = $arr_one[1];

        if ($ip_long >= $begin && $ip_long <= $end) {
            return true;
        }
    }
    return false;
}

//得到一个hash中对应key的value
function hash_get($hash_name,$key_name){
    $redis_link = new \Redis();
    $redis_link->connect(REDIS_SERVER,REDIS_PORT);
    $str = $redis_link->hget($hash_name, $key_name);
    $arr = json_decode($str,true);
    return $arr;
}

?>

#!/bin/bash
#variables

work_dir="/home/www/dx/source/plugin/ip";
#work_dir="/raidvar/www/dx/source/plugin/ip";

ip_txt_path="${work_dir}/china_ip.txt";
ip_url="http://ftp.apnic.net/apnic/stats/apnic/delegated-apnic-latest";
php_path="/usr/local/php/bin/php";
script_path="${work_dir}/putip2redis.php";

#mv old txt

cur_time=$(date +"%Y%m%d%H%M%S");
if [ -f ${ip_txt_path} ];then
    mv ${ip_txt_path} ${ip_txt_path}_${cur_time};
fi

#download

/usr/bin/curl ${ip_url} | grep ipv4 | grep CN | awk -F\| '{ printf("%s/%d\n", $4, 32-log($5)/log(2)) }' >${ip_txt_path}

#parse 2 redis

echo "begin parse ip\n";
${php_path} ${script_path}

## Changelog

`0.1` 实现crontab基本功能，实现秒级控制，记录日志和内存占用

## About

基于PHP实现的CRONTAB，可以精确到秒级的执行过程，完全兼容Linux系统的crontab写法。

## Install

    git clone git://github.com/hfcorriez/php-crontab.git   
    cd php-crontab   
    chmod -R 777 logs/   
    nohup php job.php &

## Usage

任务
____
    vim tasks

配置
____
config.ini: 
    task_file = tasks               //Task列表   
    log_error = 1                   //是否开启日志   
    log_error_file = logs/%s.log    //日志记录位置   
    php_runtime = php               //PHP执行路径
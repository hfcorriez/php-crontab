## Changelog

`0.1` 实现crontab基本功能，实现秒级控制，记录日志和内存占用
`0.2` 按照PSR重构，支持File和Database的Adapter模式

## About

基于PHP实现的CRONTAB，可以精确到秒级的执行过程，完全兼容Linux系统的crontab写法。

About Crontab... [crontab.org](http://crontab.org/)

## Install

    git clone git://github.com/hfcorriez/php-crontab.git   
    cd php-crontab
    ./bin/crontab

## Usage

复制配置
____

    mv bin/crontab.ini.example bin/crontab.ini

修改配置
____
bin/crontab.ini:

    [crontab]
    crontab.mode = file             // Crontab Adapter Path

    [file]
    file.path = tasks               // File Path

    [database]
    database.dsn = mysql:host=localhost;port=3306;dbname=youdbname;charset=UTF8
    database.username = username
    database.password = password
    database.table = php_crontab

    [log]
    log.path = /var/log/crontab.log // Log Path
    log.enable = 1

    [php]
    php.runtime = php               // PHP Runtime Path
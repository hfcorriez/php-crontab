## Changelog

- `0.1` 实现crontab基本功能，实现秒级控制，记录日志和内存占用
- `0.2` 优化和改进，分离配置使可以单独配置文件路径，日志等
- `0.3` 按照PSR重构，支持文件和数据库模式
- `0.4` 支持结果上报，可以上报到数据库或者文件

# Features

- 语法和crontab相同
- 精确到秒级的控制
- 每个任务都使用独立进程
- 支持PSR标准
- 支持文件
- 支持数据库

## About

基于PHP实现的CRONTAB，可以精确到秒级的执行过程，完全兼容Linux系统的crontab写法。

About Crontab... [crontab.org](http://crontab.org/)

## Install

    git clone git://github.com/hfcorriez/php-crontab.git
    cd php-crontab

## Usage

复制配置
____

    cp bin/crontab.ini.example bin/crontab.ini

修改配置
____
bin/crontab.ini:

    [crontab]
    crontab.adapter = file
    crontab.reporter = file1

    [file]
    file.mode = file
    file.path = tasks

    [file1]
    file1.mode = file
    file1.path = report.log

    [database]
    database.mode = database
    database.dsn = "mysql:host=localhost;port=3306;dbname=dbname;charset=UTF-8"
    database.username = username
    database.password = password
    database.table = php_crontab
    database.field = command

    [database1]
    database1.mode = database
    database1.dsn = "mysql:host=localhost;port=3306;dbname=dbname;charset=UTF-8"
    database1.username = username
    database1.password = password
    database1.table = report

    [log]
    log.path = crontab.log
    log.enable = 1


运行例子
____

    cp bin/tasks.example bin/tasks
    chmod +x bin/crontab
    ./bin/crontab --master

## Database `需要PHP PDO支持`
从0.4开始支持结果上报，有File和Database两种模式。
在0.3也支持任务列表从数据库中读取

    MySQL可以从 readme/crontab.sql 导入


<?php

if (PHP_SAPI !== 'cli') exit('CronTab only run under cli mode.');

if (!defined('CRON_DIR')) define('CRON_DIR', __DIR__);
if (!defined('CRON_INI')) define('CRON_INI', CRON_DIR . '/crontab.ini');

use CodeGun\CronTab\Executor;
use CodeGun\Ini\Parser;

// Get config.
$config = Parser::loadFromFile(CRON_INI)->get();

if (array_search('--master', $argv)) {
    $executor = new Executor(array(
        'max_process'   => 1,
        'data'          => $config,
        'args'          => array('--master'),
        'work_dir'      => CRON_DIR,
    ));
} else {
    $executor = new Executor(array(
        'max_process'   => 0,
        'data'          => $config,
        'args'          => array('--job'),
        'work_dir'      => CRON_DIR,
    ));
}
$executor->run();
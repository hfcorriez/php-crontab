<?php

if (PHP_SAPI !== 'cli') exit('CronTab only run under cli mode.');

if (!defined('CRON_DIR')) define('CRON_DIR', __DIR__);
if (!defined('CRON_INI')) define('CRON_INI', CRON_DIR . '/crontab.ini');

use CodeGun\Component\CronTab\Executor;
use CodeGun\Util\Ini\Parser;

// Change current work dir.
chdir(CRON_DIR);

// Get config.
$config = Parser::loadFromFile(CRON_INI)->get();

if (array_search('--master', $argv)) {
    $executor = new Executor(array(
        'max_process'   => 1,
        'data'          => $config,
        'args'          => array('--master'),
    ));
} else {
    $executor = new Executor(array(
        'max_process'   => 0,
        'data'          => $config,
        'args'          => array('--job'),
    ));
}
$executor->run();
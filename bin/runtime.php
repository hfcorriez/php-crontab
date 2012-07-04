<?php

if (PHP_SAPI !== 'cli') exit('CronTab only run under cli mode.');

if (!defined('CRON_DIR')) define('CRON_DIR', dirname(__FILE__));
if (!defined('CRON_INI')) define('CRON_INI', CRON_DIR . '/crontab.ini');
if (!defined('CRON_EXECUTOR')) define('CRON_EXECUTOR', __FILE__);

// Change current work dir
chdir(CRON_DIR);

// Master trip
$cron_master = false;
if ($index = array_search('--master', $argv)) {
    unset($argv[$index]);
    $argv = array_values($argv);
    $cron_master = true;
}
$argv0 = array_shift($argv);
$commands = array_values($argv);

// Init cron
require dirname(dirname(__FILE__)) . '/src/AutoLoader.php';
AutoLoader::register();
$ini = \CronTab\IniParser::loadFromFile(CRON_INI)->get();
$cron = new \CronTab\CronTab($ini);
$cron->registerExecutor(CRON_EXECUTOR);

// Work mode
if ($commands) {
    if (count($commands) > 1) {
        foreach ($commands as $command) {
            $cron->dispatch($command);
        }
        exit;
    }

    $cron->execute($commands[0]);
    exit;
}

if ($cron_master) {
    // Pulse trigger
    if ((int)shell_exec('ps -ef | grep "' . $argv0 . ' --master" | grep -v grep | wc -l') > 1) {
        exit;
    }

    // Start cron
    $cron->start();
} else {
    die ('[Usage]: use "' . $argv0 . ' --master".' . PHP_EOL);
}

<?php
/**
 * CRONTAB
 *
 * @author    hfcorriez@gmail.com
 * @todo      crontab重启
 * @todo      制作linux启动脚本
 * @todo      多用户
 */
if (PHP_SAPI !== 'cli') exit('CronTab only run under cli mode.');

error_reporting(E_ALL & ~E_NOTICE);
define('CRONTAB_DIR', dirname(__FILE__));
define('CRONTAB_START_TIME', time());
define('CRONTAB_START_MEMORY', memory_get_usage());
define('CRONTAB_USER', get_current_user());

// Change current work dir
chdir(CRONTAB_DIR);

// Work mode
if ($argc > 1)
{
    if ($argc > 2)
    {
        unset($argv[0]);
        foreach ($argv as $command)
        {
            pipe_shell(config('php_runtime') . ' ' . __FILE__ . ' ' . $command);
        }
        exit;
    }

    $command = base64_decode($argv[1]);
    if (!$command) write_log('<0> <Invalid command!>', 'work');
    $stdout = $stderr = null;
    $status = shell($command, $stdout, $stderr);

    write_log("<{$status}> <{$command}>" . ($stdout ? ' stdout: ' . $stdout : '') . ($stderr ? ' stderr: ' . $stderr : ''), 'work');
    exit;
}

// Job mode
// Pulse trigger
if ((int)shell_exec('ps -ef | grep "crontab.php" | grep -v grep | wc -l') > 1)
{
    write_log('pulse trigger checked ok.', 'run');
    exit;
}
else
{
    write_log('job start.', 'run');
}

require 'cronlib.php';

// Job run
while (true)
{
    // update or get files
    clearstatcache();
    if (file_exists(config('task_file')))
    {
        $tasks = file(config('task_file'));
    }
    else
    {
        $tasks = array();
    }

    // record current time
    $microtime = floor(microtime(true) * 1000000);
    // commands to run
    $command_hits = array();

    foreach ($tasks as $task)
    {
        $task = trim($task);

        if (!$parse = crontab_parse_line($task))
        {
            continue;
        }
        list($rule, $command) = $parse;
        if (crontab_is_valid($rule, CRONTAB_START_TIME))
        {
            $command_hits[] = $command;
        }
    }

    foreach ($command_hits as $key => $command)
    {
        $command_hits[$key] = base64_encode($command);
        write_log("<" . CRONTAB_USER . "> <{$command}>", 'job');
    }

    if ($command_hits) pipe_shell(config('php_runtime') . ' ' . __FILE__ . ' ' . join(' ', $command_hits));

    // check sleep time and do sleep
    $sleep_time = 1000000 - floor(microtime(true) * 1000000) + $microtime;
    if ($sleep_time > 0)
    {
        usleep($sleep_time);
    }
    //echo $sleep_time . PHP_EOL;
    unset($sleep_time, $microtime, $tasks, $command_hits);
}

/**
 * Functions
 */

function config($key = null)
{
    static $config = null;
    if (!$config)
    {
        $config = parse_ini_file(CRONTAB_DIR . '/config.ini');
    }

    if ($key)
    {
        return isset($config[$key]) ? $config[$key] : null;
    }
    else
    {
        return $config;
    }
}

function write_log($message, $type = 'error')
{
    error_log('[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL, 3, sprintf(config('log_error_file'), $type));
}

function shell($cmd, &$stdout, &$stderr)
{
    $descriptorspec = array
    (
        1 => array("pipe", "w"),
        2 => array("pipe", "w")
    );

    $stdout = $stderr = $status = null;
    $process = proc_open($cmd, $descriptorspec, $pipes);

    if (is_resource($process))
    {
        while (!feof($pipes[1]))
        {
            $stdout .= fgets($pipes[1], 1024);
        }
        fclose($pipes[1]);

        while (!feof($pipes[2]))
        {
            $stderr .= fgets($pipes[2], 1024);
        }
        fclose($pipes[2]);

        $status = proc_close($process);
    }

    return $status;
}

function pipe_shell($cmd)
{
    return pclose(popen((strtolower(substr(PHP_OS, 0, 3)) == 'win' ? 'start /b ' : '') . $cmd . ' &', 'r'));
}

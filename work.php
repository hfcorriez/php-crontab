<?php
/**
 * Cronjob工作进程
 * 
 * @author	hfcorriez@gmail.com
 * @todo	超时机制
 */
define('CRONJOB_DIR', dirname(__FILE__));
require CRONJOB_DIR . '/inc/lib.php';

//$tasks = json_decode($argv);
// arrange job
if ($argc > 2)
{
    unset($argv[0]);
    foreach ($argv as $command)
    {
        pipe_shell(config('php_runtime') . ' ' . __FILE__ . ' "' . str_replace('"', '\\"', $command) . '"');
    }
}
else
{
    $command = $argv[1];
    $stdout = $stderr = null;
    $status = shell($command, $stdout, $stderr);
    
    write_log("<{$status}> <{$command}>" . ($stdout ? ' stdout: ' . $stdout : '') . ($stderr ? ' stderr: ' . $stderr : ''), 'work');
}
exit;
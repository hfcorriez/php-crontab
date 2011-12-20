<?php
/**
 * Cronjob依赖库文件
 * @author	hfcorriez@gmail.com
 */

if (!defined('CRONJOB_DIR'))
{
   exit('cronjob must run under cronjob dir.'); 
}
if (PHP_SAPI !== 'cli')
{
	exit('cronjob only run under cli mode.');
}

function config($key = null)
{
	static $config = null;
	if(!$config){
		$config = parse_ini_file(CRONJOB_DIR . '/config.ini');	
	}
	
	if($key)
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

function is_time_range($index, $time)
{
    switch($index)
    {
        case 0:
            if ($time >= 0 && $time < 60)
            {
                return true;
            }
            break;
        case 1:
            if ($time >= 0 && $time < 60)
            {
                return true;
            }
            break;
        case 2:
            if ($time >= 0 && $time < 24)
            {
                return true;
            }
            break;
        case 3:
            if ($time >= 0 && $time <= 31)
            {
                return true;
            }
            break;
        case 4:
            if ($time >= 0 && $time <= 12)
            {
                return true;
            }
            break;
        case 5:
            if ($time >= 0 && $time < 7)
            {
                return true;
            }
            break;
    }
    return false;
}

function shell($cmd, &$stdout, &$stderr)
{
    $descriptorspec = array
    (
        1 => array("pipe", "w"),
        2 => array("pipe", "w")
    );

    $stdout = $stderr = null;
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
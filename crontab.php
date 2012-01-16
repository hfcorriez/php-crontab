<?php
/**
 * CRONTAB主进程文件
 * 
 * @author	hfcorriez@gmail.com
 * @todo	crontab检查进程和重启
 * @todo	制作linux启动脚本
 */
if (PHP_SAPI !== 'cli') exit('CronTab only run under cli mode.');

error_reporting(E_ALL & ~E_NOTICE);
define('CRONTAB_DIR', dirname(__FILE__));
define('CRONTAB_START_TIME', time());
define('CRONTAB_START_MEMORY', memory_get_usage());
define('CRONTAB_USER', get_current_user());

// Work mode
if ($argc > 1)
{
    if ($argc > 2)
    {
        unset($argv[0]);
        foreach ($argv as $command)
        {
            pipe_shell(config('php_runtime') . ' ' . __FILE__ . ' "' . str_replace('"', '\\"', $command) . '"');
        }
        exit;
    }
    
    $command = $argv[1];
    $stdout = $stderr = null;
    $status = shell($command, $stdout, $stderr);

    write_log("<{$status}> <{$command}>" . ($stdout ? ' stdout: ' . $stdout : '') . ($stderr ? ' stderr: ' . $stderr : ''), 'work');
    exit;
}

// Job mode
// Pulse trigger
if((int)shell_exec('ps -ef | grep "crontab.php" | grep -v grep | wc -l') > 1)
{
    write_log('pulse trigger checked ok.', 'run');
    exit;
}
else
{
    write_log('job start.', 'run');
}

$index_map = array('s', 'i', 'H', 'w', 'd', 'm');

// Job run
while (true)
{
    write_log('Memory: ' . (memory_get_usage() - CRONTAB_START_MEMORY), 'memory');
    
    // update or get files
	clearstatcache();
    if(file_exists(config('task_file')))
    {
	    $tasks = file(config('task_file'));
    }
    else
    {
        $tasks = array();
    }

	// record current time
	$time = time();
	$microtime = floor(microtime(true) * 1000000);
	// commands to run
	$command_hits = array();
	
	foreach ($tasks as $task)
	{
		$task = trim($task);
		// ignore blank line and comment line
		if ($task == '' || $task{0} == '#')
		{
			continue;
		}
		
		// check format
		if (!preg_match('/^((\*|\d+|\d+\-\d+|[\d,]+)(\/\d+)? ){6}(.*)$/', $task, $match))
		{
			write_log('Error: format ' . $task);
		}
		
		// get command and cycles
		$command = $match[4];
		$cycles = explode(' ', trim(substr($task, 0, -(strlen($command)))));
		
		// init cycle hits to record task cycle.
		$cycle_hits = array();
		foreach ($cycles as $index => $cycle)
		{
		    // pre chunk
			$is_time_pre = false;
			// sub chunck
			$is_time_sub = false;
			
			list($time_pre, $time_sub) = explode('/', $cycle);
			
			// if pre is *, pre is ok.
			if ($time_pre == '*') {
				$is_time_pre = true;
			}
			// if pre include "-" then star range mode.
			elseif (strpos($time_pre, '-') !== false)
			{
				list($min, $max) = explode('-', $time_pre);
				// min, max must be under rules.
				if(!is_time_range($index, $min) || !is_time_range($index, $max) || $max <= $min)
				{
				    write_log('Error: time pre range ' . $task);
			        continue 2;
				}
				$time_current = date($index_map[$index]);
				// check range
				if ($time_current >= $min && $time_current <= $max)
				{
					$is_time_pre = true;
				}
				unset($time_current, $min, $max);
			}
			elseif (strpos($time_pre, ',') !== false)
			{
			    $time_points = explode(',', $time_pre);
			    $time_current = date($index_map[$index]);
			    if(array_search($time_current, $time_points) !== false)
			    {
			        $is_time_pre = true;
			    }
				unset($time_current, $time_points);
			}
			else
			{
			    write_log('Error: time pre is wrong with->' . $task);
			    continue 2;
			}
			
			// not exist sub time.
			if (!$time_sub)
			{
				$is_time_sub = true;
				if (!$is_time_pre)
				{
				    // check time range
    				if(!is_time_range($index, $time_pre))
    				{
    				    write_log('Error: time pre range ' . $task);
    				    break;
    				}
    				// if time on then pre is ok.
					if (is_numeric($time_pre) && $time_pre == date('s'))
					{
						$is_time_pre = true;
					}
					else
					{
						break;
					}
				}
				
			}
			else
			{
				if (!$is_time_pre)
				{
					break;
				}
				// check sub range 
			    if(!is_time_range($index, $time_sub))
				{
				    write_log('Error: time sub range ' . $task);
				    break;
				}

				$time_sub = (int) $time_sub;
				// check every cycle
				switch ($index)
				{
					case 0:
					    // second check.
						if (($time - CRONTAB_START_TIME) % $time_sub == 0)
						{
							$is_time_sub = true;
						}
						break;
					case 1:
					    // minutes check
						if (floor(($time - CRONTAB_START_TIME)/60) % $time_sub == 0)
						{
							$is_time_sub = true;
						}
						break;
					case 2:
					    // hour check
						if (floor(($time - CRONTAB_START_TIME)/3600) % $time_sub == 0)
						{
							$is_time_sub = true;
						}
						break;
					case 3:
					    // day check
						if (floor(($time - CRONTAB_START_TIME)/86400) % $time_sub == 0)
						{
							$is_time_sub = true;
						}
						break;
					case 4:
					    // month check
						$date1 = explode('-', date('Y-m', CRONTAB_START_TIME));
						$date2 = explode('-', date('Y-m', $time));
						$month = abs($date1[0] - $date2[0]) * 12 + abs($date1[1] - $date2[1]);
						if ($month & $time_sub == 0)
						{
							$is_time_sub = true;
						}
						unset($date1, $date2, $month);
						break;
					case 5:
					    // week check
						if (floor(($time - CRONTAB_START_TIME)/86400/7) % $time_sub == 0)
						{
							$is_time_sub = true;
						}
						break;
				}
			}
			
			// pre and sub is ok, then hit one
			if($is_time_pre && $is_time_sub)
			{
				$cycle_hits[$index] = 1;
			}
			else 
			{
				break;
			}
			
			unset($time_pre, $time_sub, $is_time_pre, $is_time_sub);
		}
		
		// run command in pip mode when every cycle is hit.
		if(array_sum($cycle_hits) == 6)
		{
		    $command_hits[] = $command;
		}
		
		unset($cycles, $cycle_hits, $match, $command);
	}
	
	foreach ($command_hits as $key => $command)
	{
	    $command_hits[$key] = '"' . str_replace('"', '\\"', $command) . '"';
	    write_log("<" . CRONTAB_USER . "> <{$command}>", 'job');
	}
	
	if($command_hits) pipe_shell(config('php_runtime') . ' ' . __FILE__ . ' ' . join(' ', $command_hits));
		
	// check sleep time and do sleep
	$sleep_time = 1000000 - floor(microtime(true)*1000000) + $microtime;
	if($sleep_time > 0)
	{
	    usleep($sleep_time);
	}
	//echo $sleep_time . PHP_EOL;
	unset($sleep_time, $time, $microtime, $tasks, $command_hits);
}

/**
 * Functions
 */

function config($key = null)
{
    static $config = null;
    if(!$config){
        $config = parse_ini_file(CRONTAB_DIR . '/config.ini');
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
<?php

namespace CronTab;

class CronLib
{
    /**
     * Parse line
     *
     * @param $line
     * @return array|bool
     */
    public static function parseLine($line)
    {
        $line = trim($line);
        // ignore blank line and comment line
        if ($line == '' || $line{0} == '#') {
            return false;
        }

        // check format
        if (!preg_match('/^((\*|\d+|\d+\-\d+|[\d,]+)(\/\d+)? ){6}(.*)$/', $line, $match)) {
            return false;
        }

        $command = $match[4];
        $rule = trim(substr($line, 0, -(strlen($command))));
        return array($rule, $command);
    }

    /**
     * Is valid command
     *
     * @static
     * @param $rule
     * @param $start_time
     * @param bool $time
     * @return bool
     */
    public static function isValid($rule, $start_time, $time = false)
    {
        if (!$time) $time = time();
        static $index_map = array('s', 'i', 'H', 'w', 'd', 'm');
        // get command and cycles
        $cycles = explode(' ', trim($rule));

        // init cycle hits to record task cycle.
        $cycle_hits = array();
        foreach ($cycles as $index => $cycle) {
            // pre chunk
            $is_time_pre = false;
            // sub chunck
            $is_time_sub = false;

            list($time_pre, $time_sub) = explode('/', $cycle);

            // if pre is *, pre is ok.
            if ($time_pre == '*') {
                $is_time_pre = true;
            } // if pre include "-" then star range mode.
            elseif (strpos($time_pre, '-') !== false) {
                list($min, $max) = explode('-', $time_pre);
                // min, max must be under rules.
                if (!self::checkRange($index, $min) || !self::checkRange($index, $max) || $max <= $min) {
                    return false;
                }
                $time_current = date($index_map[$index]);
                // check range
                if ($time_current >= $min && $time_current <= $max) {
                    $is_time_pre = true;
                }
                unset($time_current, $min, $max);
            }
            elseif (strpos($time_pre, ',') !== false) {
                $time_points = explode(',', $time_pre);
                $time_current = date($index_map[$index]);
                if (array_search($time_current, $time_points) !== false) {
                    $is_time_pre = true;
                }
                unset($time_current, $time_points);
            }
            else {
                return false;
            }

            // not exist sub time.
            if (!$time_sub) {
                $is_time_sub = true;
                if (!$is_time_pre) {
                    // check time range
                    if (!self::checkRange($index, $time_pre)) {
                        return false;
                    }
                    // if time on then pre is ok.
                    if (is_numeric($time_pre) && $time_pre == date('s')) {
                        $is_time_pre = true;
                    } else {
                        break;
                    }
                }

            } else {
                if (!$is_time_pre) {
                    break;
                }
                // check sub range
                if (!self::checkRange($index, $time_sub)) {
                    return false;
                }

                $time_sub = (int)$time_sub;
                // check every cycle
                switch ($index) {
                    case 0:
                        // second check.
                        if (($time - $start_time) % $time_sub == 0) {
                            $is_time_sub = true;
                        }
                        break;
                    case 1:
                        // minutes check
                        if (floor(($time - $start_time) / 60) % $time_sub == 0) {
                            $is_time_sub = true;
                        }
                        break;
                    case 2:
                        // hour check
                        if (floor(($time - $start_time) / 3600) % $time_sub == 0) {
                            $is_time_sub = true;
                        }
                        break;
                    case 3:
                        // day check
                        if (floor(($time - $start_time) / 86400) % $time_sub == 0) {
                            $is_time_sub = true;
                        }
                        break;
                    case 4:
                        // month check
                        $date1 = explode('-', date('Y-m', $start_time));
                        $date2 = explode('-', date('Y-m', $time));
                        $month = abs($date1[0] - $date2[0]) * 12 + abs($date1[1] - $date2[1]);
                        if ($month & $time_sub == 0) {
                            $is_time_sub = true;
                        }
                        unset($date1, $date2, $month);
                        break;
                    case 5:
                        // week check
                        if (floor(($time - $start_time) / 86400 / 7) % $time_sub == 0) {
                            $is_time_sub = true;
                        }
                        break;
                }
            }

            // pre and sub is ok, then hit one
            if ($is_time_pre && $is_time_sub) {
                $cycle_hits[$index] = 1;
            } else {
                break;
            }

            unset($time_pre, $time_sub, $is_time_pre, $is_time_sub);
        }

        // run command in pip mode when every cycle is hit.
        if (array_sum($cycle_hits) == 6) {
            return true;
        }
        return false;
    }

    /**
     * Check Range
     *
     * @param $index
     * @param $time
     * @return bool
     */
    public static function checkRange($index, $time)
    {
        switch ($index) {
            case 0:
                if ($time >= 0 && $time < 60) {
                    return true;
                }
                break;
            case 1:
                if ($time >= 0 && $time < 60) {
                    return true;
                }
                break;
            case 2:
                if ($time >= 0 && $time < 24) {
                    return true;
                }
                break;
            case 3:
                if ($time >= 0 && $time <= 31) {
                    return true;
                }
                break;
            case 4:
                if ($time >= 0 && $time <= 12) {
                    return true;
                }
                break;
            case 5:
                if ($time >= 0 && $time < 7) {
                    return true;
                }
                break;
        }
        return false;
    }

    /**
     * Run shell
     *
     * @param $cmd
     * @param $stdout
     * @param $stderr
     * @return int|null
     */
    public static function shell($cmd, &$stdout, &$stderr)
    {
        $descriptorspec = array
        (
            1 => array("pipe", "w"),
            2 => array("pipe", "w")
        );

        $stdout = $stderr = $status = null;
        $process = proc_open($cmd, $descriptorspec, $pipes);

        if (is_resource($process)) {
            while (!feof($pipes[1])) {
                $stdout .= fgets($pipes[1], 1024);
            }
            fclose($pipes[1]);

            while (!feof($pipes[2])) {
                $stderr .= fgets($pipes[2], 1024);
            }
            fclose($pipes[2]);

            $status = proc_close($process);
        }

        return $status;
    }

    /**
     * Run shell in pipe
     *
     * @static
     * @param $cmd
     * @return int
     */
    public static function pipeShell($cmd)
    {
        return pclose(popen((strtolower(substr(PHP_OS, 0, 3)) == 'win' ? 'start /b ' : '') . $cmd . ' &', 'r'));
    }
}
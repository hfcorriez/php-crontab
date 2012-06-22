<?php

namespace CronTab\Reporter;

/**
 * File Reporter
 */
class File extends \CronTab\Reporter
{
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Report
     *
     */
    function report(array $report = array())
    {
        $text = '';
        foreach ($report as $k => $v) {
            if ($k == 'start_time') $v = date('Y-m-d H:i:s.' . substr($v, strpos($v, '.') + 1, 3), $v);
            $text .= '[' . str_pad($k, 20, ' ', STR_PAD_BOTH) . '] ' . $v . PHP_EOL;
        }
        $text .= str_repeat('-', 50) . PHP_EOL;
        file_put_contents($this->config['path'], $text, FILE_APPEND);
    }
}
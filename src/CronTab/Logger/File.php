<?php

namespace CronTab\Logger;

class File extends \CronTab\Logger
{
    protected $config = array();

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function log($text)
    {
        $micro_time = microtime(true);
        $datetime = date('Y-m-d H:i:s.' . substr(sprintf('%.3f', $micro_time), -3), $micro_time);

        $msg = "[$datetime] $text\n";
        file_put_contents($this->config['path'], $msg, FILE_APPEND);
    }
}

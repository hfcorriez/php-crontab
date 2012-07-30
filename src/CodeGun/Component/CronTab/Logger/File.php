<?php

namespace CodeGun\Component\CronTab\Logger;

class File extends \CodeGun\Component\CronTab\Logger
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
        $dir = dirname($this->config['path']);
        if (!is_dir($dir) && !mkdir($dir, 0777, true)) return false;
        return file_put_contents($this->config['path'], $msg, FILE_APPEND);
    }
}

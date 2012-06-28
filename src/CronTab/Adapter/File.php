<?php

namespace CronTab\Adapter;

/**
 * File Adapter
 */
class File extends \CronTab\Adapter
{
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function getTasks()
    {
        clearstatcache();
        if (!is_file($this->config['path'])) throw new \Exception("Non-exist task file \"{$this->config['path']}\"");

        $tasks = file($this->config['path']);

        return $tasks;
    }
}
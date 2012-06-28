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

        foreach ($tasks as $key => $task) {
            unset($tasks[$key]);
            $task = trim($task);

            if (!$parse = \CronTab\CronLib::parseLine($task)) continue;

            $tasks[$key] = array($parse[0], $parse[1]);
        }
        return $tasks;
    }
}
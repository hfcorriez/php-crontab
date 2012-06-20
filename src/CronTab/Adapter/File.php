<?php

namespace CronTab\Adapter;

/**
 * File Adapter for tasks
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
            $task = trim($task);

            if (!$parse = \CronTab\CronLib::parseLine($task)) {
                continue;
            }

            unset($tasks[$key]);
            $tasks[$key] = array($parse[0], $parse[1]);
        }
        return $tasks;
    }
}
<?php

namespace CronTab\Adapter;

class File extends \CronTab\Adapter
{
    protected $file;

    public function __construct($file)
    {
        $this->file = $file;
    }

    public function getTasks()
    {
        clearstatcache();
        if (!is_file($this->file)) throw new \Exception("Non-exist task file \"$this->file\"");

        $tasks = file($this->file);

        foreach ($tasks as $key => $task) {
            $task = trim($task);

            if (!$parse = \CronTab\CronLib::parseLine($task)) {
                continue;
            }

            list($rule, $command) = $parse;
            unset($tasks[$key]);
            $tasks[$key] = array($rule, $command);
        }
        return $tasks;
    }
}
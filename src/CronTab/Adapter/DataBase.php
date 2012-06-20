<?php

namespace CronTab\Adapter;

/**
 * DataBase Adapter for tasks
 */
class Database extends \CronTab\Adapter
{
    protected $config;

    protected $pdo;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->pdo = new \PDO($config['dsn'], $config['username'], $config['password']);
    }

    public function getTasks()
    {
        $field = $this->config['field'];
        $tasks = $this->pdo->query("SELECT $field FROM {$this->config['table']}");
        foreach ($tasks as $key => $task) {
            $task = trim($task[$field]);

            if (!$parse = \CronTab\CronLib::parseLine($task)) {
                continue;
            }

            unset($tasks[$key]);
            $tasks[$key] = array($parse[0], $parse[1]);
        }
        return $tasks;
    }
}
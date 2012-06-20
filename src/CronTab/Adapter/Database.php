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
        $tasks = array();
        $results = $this->pdo->query("SELECT $field FROM {$this->config['table']}");
        foreach ($results as $result) {
            $task = trim($result[$field]);

            if (!$parse = \CronTab\CronLib::parseLine($task)) {
                continue;
            }

            $tasks[] = array($parse[0], $parse[1]);
        }
        return $tasks;
    }
}
<?php

namespace CodeGun\Component\CronTab\Adapter;

/**
 * DataBase Adapter
 */
class Database extends \CodeGun\Component\CronTab\Adapter
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
            $tasks[] = trim($result[$field]);
        }
        return $tasks;
    }
}
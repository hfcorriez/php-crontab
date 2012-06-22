<?php

namespace CronTab\Reporter;

/**
 * Database Reporter
 */
class Database extends \CronTab\Reporter
{
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->pdo = new \PDO($config['dsn'], $config['username'], $config['password']);

    }

    /**
     * Report
     *
     */
    function report(array $report = array())
    {
            $statement = $this->pdo->prepare("INSERT INTO {$this->config['table']}(`" . join('`,`', array_keys($report)) . "`) VALUES(" . join(',', array_fill(0, count($report), ' ? ')) . ")");
            $statement->execute(array_values($report));
    }
}
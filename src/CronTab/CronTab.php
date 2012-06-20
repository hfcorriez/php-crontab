<?php

namespace CronTab;

class CronTab
{
    protected $start_time;
    protected $executor;

    /**
     * @var Adapter\Database|Adapter\File
     */
    protected $adapter;

    /**
     * @var Logger
     */
    protected $logger;

    protected $config = array();
    protected $tasks = array();

    /**
     * Constructor
     *
     * @param $config
     * @throws \Exception
     */
    public function __construct($config)
    {
        $this->config = $config;
        $this->start_time = time();
        $this->logger = new Logger($this->config['log']);
    }

    /**
     * Register executor for execute
     *
     * @param $executor
     */
    public function registerExecutor($executor)
    {
        $this->executor = $executor;
    }

    /**
     * Start to run
     */
    public function start()
    {
        while (true) {
            // Load tasks every time.
            $this->loadTask();

            // record current time
            $micro_time = floor(microtime(true) * 1000000);

            // commands to run
            $command_hits = array();

            foreach ($this->tasks as $task) {
                list($rule, $command) = $task;
                if (CronLib::isValid($rule, $this->start_time)) {
                    $command_hits[] = $command;
                }
            }

            foreach ($command_hits as $key => $command) {
                $command_hits[$key] = base64_encode($command);
                $this->logger->write("<{$command}> dispatch.");
            }

            if ($command_hits) $this->dispatch(join(' ', $command_hits));

            // check sleep time and do sleep
            $sleep_time = 1000000 - floor(microtime(true) * 1000000) + $micro_time;
            if ($sleep_time > 0) {
                usleep($sleep_time);
            }

            unset($sleep_time, $micro_time, $tasks, $command_hits);
        }
    }

    /**
     * Load Tasks
     */
    public function loadTask()
    {
        if (!$this->adapter) {
            switch ($this->config['crontab']['mode']) {
                case 'file':
                    $this->adapter = new Adapter\File($this->config['file']);
                    break;
                case 'database':
                    $this->adapter = new Adapter\Database($this->config['database']);
                    break;
                default:
                    throw new \Exception("Unkown adapter mode: {$this->config['crontab']['mode']}.");
            }
        }

        $this->tasks = $this->adapter->getTasks();
    }

    /**
     * Dispath command
     *
     * @param $command
     */
    public function dispatch($command)
    {
        CronLib::pipeShell($this->executor . ' ' . $command);
    }

    /**
     * Execute command
     *
     * @param array $commands
     * @return mixed
     */
    public function execute($command)
    {
        $command = base64_decode($command);
        if (!$command) $this->logger->write('<0> <Invalid command!>');
        $out = $err = null;
        $status = CronLib::shell($command, $out, $err);

        $this->logger->write("<{$status}> <{$command}>" . ($err ? ' err:' . $err : ''));
    }
}
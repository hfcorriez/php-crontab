<?php

namespace CodeGun\Component\CronTab;

class Executor
{
    protected $_ = null;
    protected $php = null;
    protected $args = array();
    protected $config = array();

    public function __construct(array $config)
    {
        if (PHP_SAPI !== 'cli') throw new \Exception('Please use this under cli mode.');

        $this->config = $config;
        $this->_ = $GLOBALS['argv'][0];
        $args = array_slice($GLOBALS['argv'], 1);
        if (substr($_SERVER['_'], -strlen($this->_)) != $this->_) {
            $this->php = $_SERVER['_'];
        }

        foreach ($args as $arg) {
            $pos = strpos($arg, '=');
            $v = true;
            if ($pos !== false) {
                $k = substr($arg, 0, $pos);
                $v = substr($arg, $pos + 1);
            } else {
                $k = $arg;
            }
            $this->args[$k] = $v;
            if (strpos($v, ',') !== false) $this->args[$k] = explode(',', $v);
        }

        if ($this->checkIfProcessMax()) exit('Over max process.');
        if (!$this->checkArgs()) exit($this->usage());
    }

    public function run()
    {
        try {
            $cron = new CronTab($this->config['data']);
            $cron->registerExecutor(!empty($this->config['executor']) ? $this->config['executor'] : trim($this->php . ' ' . $this->_ . ' --job='));

            if (isset($this->args['--master'])) {
                $cron->start();
            } elseif (!empty($this->args['--job'])) {
                if (is_array($this->args['--job'])) {
                    foreach ($this->args['--job'] as $command) {
                        $cron->dispatch($command);
                    }
                } else {
                    $cron->execute($this->args['--job']);
                }
            } else {
                $this->usage();
            }

        } catch (\Exception $e) {
        }
    }

    public function checkArgs()
    {
        if ($this->config['args']) {
            foreach ($this->config['args'] as $arg) {
                if (!isset($this->args[$arg])) return false;
            }
        }
        return true;
    }

    public function checkIfProcessMax()
    {
        return !empty($this->config['max_process']) && $this->getProcessCount() >= $this->config['max_process'] + 1;
    }

    public function getProcessCount()
    {
        $greps = array();
        foreach ($this->config['args'] as $arg) {
            $greps[] = "grep -e \"$arg\"";
        }
        return (int)shell_exec('ps -ef | grep ' . $this->_ . ' | ' . ($greps ? join(' | ', $greps) . ' |' : '') . ' grep -v grep | grep -v "sh -c" | wc -l');
    }

    public function usage()
    {
        $args = array();
        foreach ($this->config['args'] as $arg) {
            $args[] = "{$arg}[=value]";
        }
        return "[Usage] \n\n" . $this->_ . ' ' . join(' ', $args) . "\n\n";
    }
}
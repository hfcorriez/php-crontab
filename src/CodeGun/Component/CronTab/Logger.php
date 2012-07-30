<?php

namespace CodeGun\Component\CronTab;

abstract class Logger
{
    /**
     * Log text
     *
     * @abstract
     * @param $text
     * @return mixed
     */
    abstract function log($text);
}

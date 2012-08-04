<?php

namespace CodeGun\CronTab;

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

<?php

namespace CodeGun\CronTab;

/**
 * Reporter for task execute.
 */
abstract class Reporter
{
    /**
     * Report
     *
     * @abstract
     */
    abstract function report(array $report = array());
}
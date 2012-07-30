<?php

namespace CodeGun\Component\CronTab;

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
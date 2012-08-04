<?php

namespace CodeGun\CronTab;

/**
 * Adapter for tasks
 */
abstract class Adapter
{
    /**
     * Get Tasks
     *
     * @abstract
     * @return Array
     */
    abstract function getTasks();
}
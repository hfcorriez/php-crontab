<?php

namespace CodeGun\Component\CronTab;

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
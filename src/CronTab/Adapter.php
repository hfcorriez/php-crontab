<?php

namespace CronTab;

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
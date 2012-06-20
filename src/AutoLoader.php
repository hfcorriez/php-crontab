<?php

class AutoLoader
{
    public static function load($className)
    {
        $className = ltrim($className, '\\');
        $fileName = '';

        if ($lastNsPos = strripos($className, '\\')) {
            $namespace = substr($className, 0, $lastNsPos);
            $className = substr($className, $lastNsPos + 1);
            $fileName = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
        }

        $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
        $fileName = __DIR__ . DIRECTORY_SEPARATOR . $fileName;

        if (is_file($fileName)) require $fileName;
    }

    public static function register()
    {
        spl_autoload_register(array(__CLASS__, 'load'));
    }
}

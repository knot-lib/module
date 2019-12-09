<?php
require_once dirname(dirname(__DIR__)). '/vendor/autoload.php';

spl_autoload_register(function ($class)
{
    if (strpos($class, 'Calgamo\\Module\\Sample') === 0) {
        $name = substr($class, strlen('Calgamo\\Module\\Sample'));
        $name = array_filter(explode('\\',$name));
        $file = dirname(__DIR__) . '/src/' . implode('/',$name) . '.php';
        /** @noinspection PhpIncludeInspection */
        require_once $file;
    }
    else if (strpos($class, 'Calgamo\\Module\\') === 0) {
        $name = substr($class, strlen('Calgamo\\Module'));
        $name = array_filter(explode('\\',$name));
        $file = dirname(__DIR__, 2) . '/src/' . implode('/',$name) . '.php';
        /** @noinspection PhpIncludeInspection */
        require_once $file;
    }
});

<?php

namespace WpStarter\Flow\Support\Facades;

use WpStarter\Flow\FlowManager;

class Facade
{
    public static $manager;

    public static function __callStatic($method, $args)
    {
        $instance = static::getFlowManager();

        if (!$instance) {
            throw new \RuntimeException('The Flow Manager has not been set.');
        }

        return $instance->$method(...$args);
    }

    /**
     * @return FlowManager
     */
    static function getFlowManager()
    {
        return static::$manager;
    }

    static function setFlowManager($manager)
    {
        static::$manager = $manager;
    }
}
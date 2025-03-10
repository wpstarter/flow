<?php

namespace WpStarter\Flow\Support;

class Container
{
    function make($class)
    {
        return new $class();
    }

    public static function getInstance()
    {
        static $instance;
        if (!$instance) {
            $instance = new static();
        }
        return $instance;
    }
}
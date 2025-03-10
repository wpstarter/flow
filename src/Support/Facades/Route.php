<?php

namespace WpStarter\Flow\Support\Facades;

class Route extends Facade
{
    public static function match($route, $flow, $before = false)
    {
        if ($before) {
            static::getFlowManager()->router()->prepend($route, $flow);
        } else {
            static::getFlowManager()->router()->add($route, $flow);
        }
    }
}
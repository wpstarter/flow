<?php

namespace WpStarter\Flow\Support;

use WpStarter\Flow\Flow;

class Helper
{
    public static function app()
    {
        if (function_exists('app')) {
            return app();
        }
        if (function_exists('ws_app')) {
            return ws_app();
        }
        return Container::getInstance();
    }

    public static function value($value, ...$args)
    {
        return $value instanceof \Closure ? $value(...$args) : $value;
    }

    public static function filled($value)
    {
        return !self::blank($value);
    }

    public static function blank($value)
    {
        if (is_null($value)) {
            return true;
        }

        if (is_string($value)) {
            return trim($value) === '';
        }

        if (is_numeric($value) || is_bool($value)) {
            return false;
        }

        if ($value instanceof \Countable) {
            return count($value) === 0;
        }

        if (PHP_VERSION_ID >= 80000 && class_exists(\Stringable::class) && $value instanceof \Stringable) {
            return trim((string)$value) === '';
        }

        return empty($value);
    }

    public static function getFlowUniqueId($flow)
    {
        if ($flow instanceof Flow) {
            $class = get_class($flow);
            $id = $flow->getId();
            return $class . ($id ? ":$id" : "");
        }
        return $flow;
    }
    public static function getFlowChannel($flow,$default=null){
        if($flow instanceof Flow){
            return $flow->channel;
        }
        return $default;
    }

}
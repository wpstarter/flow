<?php

namespace WpStarter\Flow\State;

trait HasStore
{
    protected $store;
    protected static $storeClass;

    public static function useStore($store)
    {
        static::$storeClass = $store;
    }

    public function makeStore($key)
    {
        return new static::$storeClass($key);
    }
}
<?php

namespace WpStarter\Flow\State;

abstract class Store implements StoreContract
{
    protected $cacher;
    protected $namespace;

    public function __construct($namespace)
    {
        $this->namespace = $namespace;
    }

    function put($key, $value, $ttl = null)
    {
        return $this->cacher::put($this->namespacedKey($key), $value, $ttl);
    }

    function get($key, $default = null)
    {
        return $this->cacher::get($this->namespacedKey($key), $default);
    }

    function forever($key, $value)
    {
        return $this->cacher::forever($this->namespacedKey($key), $value);
    }

    function forget($key)
    {
        return $this->cacher::forget($this->namespacedKey($key));
    }

    function namespacedKey($key)
    {
        return $this->namespace . ':' . $key;
    }
}
<?php

namespace WpStarter\Flow\State;

class ArrayStore extends Store
{
    /**
     * The array storing the values.
     *
     * @var array
     */
    static protected $storage = [];

    /**
     * Get a value from the store.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return static::$storage[$this->namespacedKey($key)] ?? $default;
    }

    /**
     * Store a value.
     *
     * @param string $key
     * @param mixed $value
     * @param int|null $ttl
     * @return void
     */
    public function put($key, $value, $ttl = null)
    {
        static::$storage[$this->namespacedKey($key)] = $value;
    }

    /**
     * Store a value permanently.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function forever($key, $value)
    {
        $this->put($key, $value);
    }

    /**
     * Remove a value from the store.
     *
     * @param string $key
     * @return bool
     */
    public function forget($key)
    {
        unset(static::$storage[$this->namespacedKey($key)]);
        return true;
    }
}
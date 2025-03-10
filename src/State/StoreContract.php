<?php

namespace WpStarter\Flow\State;

interface StoreContract
{
    public function get($key, $default = null);

    public function put($key, $value);

    public function forever($key, $value);

    public function forget($key);
}
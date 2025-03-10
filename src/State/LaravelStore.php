<?php

namespace WpStarter\Flow\State;

use Illuminate\Support\Facades\Cache;

class LaravelStore extends Store
{
    protected $cacher = Cache::class;
} 
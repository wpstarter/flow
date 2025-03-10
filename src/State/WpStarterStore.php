<?php

namespace WpStarter\Flow\State;

use WpStarter\Support\Facades\Cache;

class WpStarterStore extends Store
{
    protected $cacher = Cache::class;
}
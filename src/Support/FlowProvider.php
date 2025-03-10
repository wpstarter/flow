<?php

namespace WpStarter\Flow\Support;

use WpStarter\Flow\FlowCollection;
use WpStarter\Flow\FlowManager;

abstract class FlowProvider
{
    protected FlowManager $manager;
    protected FlowCollection $flows;

    public function __construct(FlowManager $manager)
    {
        $this->manager = $manager;
        $this->flows = $manager->flows();
    }

    protected function loadRoutesFrom($path)
    {
        $this->manager->withRoute($path);
    }

    abstract public function register();

    public function boot()
    {
    }
}
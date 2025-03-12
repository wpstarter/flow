<?php

namespace WpStarter\Flow;

use Illuminate\Support\ServiceProvider;
class FlowServiceProviderLaravel extends ServiceProvider
{
    public function register(): void
    {
        $this->registerManager();
        $this->registerConfig();
    }
    protected function registerManager(){
        $this->app->singleton(FlowManager::class);
        $this->app->alias(FlowManager::class, 'flow.manager');
    }
    protected function registerConfig()
    {
        $config = __DIR__.'/../config/flow.php';

        $this->publishes([$config => base_path('config/flow.php')], ['flow-config', 'flow:config']);

        $this->mergeConfigFrom($config, 'flow');
    }
}
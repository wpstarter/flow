<?php

namespace WpStarter\Flow;

use WpStarter\Support\ServiceProvider;
class FlowServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerManager();
        $this->registerConfig();
    }
    function boot(FlowManager $manager){
        $manager->configureWpStarter();
        $manager->register(ws_config('flow.providers'));
    }
    protected function registerManager(){
        $this->app->singleton(FlowManager::class);
        $this->app->alias(FlowManager::class, 'flow.manager');
    }
    protected function registerConfig()
    {
        $config = __DIR__.'/../config/flow.php';

        $this->publishes([$config => ws_base_path('config/flow.php')], ['flow-config', 'flow:config']);

        $this->mergeConfigFrom($config, 'flow');
    }
}
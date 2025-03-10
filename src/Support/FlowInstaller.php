<?php

namespace WpStarter\Flow\Support;

class FlowInstaller
{

    public static function publishConfig(): void
    {
        if (class_exists('Illuminate\Support\Facades\Artisan')) {
            \Illuminate\Support\Facades\Artisan::call('vendor:publish', [
                '--provider' => 'WpStarter\Flow\FlowServiceProvider',
                '--tag' => 'config',
                '--force' => false
            ]);
        }elseif (class_exists('WpStarter\Support\Facades\Artisan')) {
            \WpStarter\Support\Facades\Artisan::call('vendor:publish', [
                '--provider' => 'WpStarter\Flow\FlowServiceProvider',
                '--tag' => 'config',
                '--force' => false
            ]);
        }
    }

}
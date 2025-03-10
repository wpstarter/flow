<?php
namespace WpStarter\Flow\Tests\Flow;
use WpStarter\Flow\Support\FlowProvider;

class TestFlowProvider extends FlowProvider
{
    public function register()
    {
        $this->flows->register(\WpStarter\Flow\Tests\Flow\LoginFlow::class);
        $this->flows->register(\WpStarter\Flow\Tests\Flow\OrderFlow::class);
        $this->flows->register(\WpStarter\Flow\Simple::make('simple')->action(function(){
            return 'simple flow';
        }));
        $this->loadRoutesFrom( __DIR__ . '/routes.php');
    }
}
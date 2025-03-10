<?php

use WpStarter\Flow\FlowManager;
use WpStarter\Flow\Simple;

test('test flow', function () {
    $flowManager = new FlowManager('general');
    $flowManager->configureStandalone();
    $flowManager->register(\WpStarter\Flow\Tests\Flow\LoginFlow::class);
    $flowManager->register(\WpStarter\Flow\Tests\Flow\OrderFlow::class);
    $request=new \WpStarter\Flow\FlowRequest(['id'=>1,'message'=>'login']);
    $response=$flowManager->run($request);
    expect($response)->toBe(['login']);
    $request=new \WpStarter\Flow\FlowRequest(['id'=>1,'message'=>'123']);
    $response=$flowManager->run($request);
    expect($response)->toBe(['order']);
    $request=new \WpStarter\Flow\FlowRequest(['id'=>1,'message'=>'hi']);
    $response=$flowManager->run($request);
    expect($response)->toBe(['order']);
    $flowManager->flows()->resetState();
    $request=new \WpStarter\Flow\FlowRequest(['id'=>1,'message'=>'hi']);
    $response=$flowManager->run($request);
    expect($response)->toBe(['login']);
    $flowManager->register(\WpStarter\Flow\Simple::make('simple')->action(function(){
        return 'simple flow';
    }));
    $request=new \WpStarter\Flow\FlowRequest(['id'=>1,'message'=>'simple']);
    $response=$flowManager->run($request);
    expect($response)->toBe('simple flow');
});

test('test duplicated flow', function () {
    $flowManager = new FlowManager('duplicated');
    $flowManager->configureStandalone();
    $flowManager->register(\WpStarter\Flow\Tests\Flow\LoginFlow::class);
    $flowManager->register(\WpStarter\Flow\Tests\Flow\LoginFlow::class);
    $request=new \WpStarter\Flow\FlowRequest();
    expect(fn() => $flowManager->run($request))
        ->toThrow(\Exception::class);
});
test('test simple flow', function () {
    $flowManager = new FlowManager('simple');
    $flowManager->configureStandalone();
    $flowManager->register(\WpStarter\Flow\Simple::make('simple1')->action(function($flow){
        $flow->redirect('simple2');
        return 'simple flow';
    }));
    $flowManager->register(\WpStarter\Flow\Simple::make('simple2')->route('test route 2')->action(function($flow){
        $flow->redirect('simple3');
        return 'simple flow2';
    }));
    $flowManager->register(\WpStarter\Flow\Simple::make('simple3')->action(function(\WpStarter\Flow\Flow $flow){
        $flow->redirectToDefault();
        return 'simple flow3';
    }));
    $request=new \WpStarter\Flow\FlowRequest();
    expect($flowManager->run($request))->toBe('simple flow');
    expect($flowManager->run($request))->toBe('simple flow2');
    expect($flowManager->run($request))->toBe('simple flow3');
    expect($flowManager->run($request))->toBe('simple flow');
    $request=new \WpStarter\Flow\FlowRequest(['message'=>'test route 2']);
    expect($flowManager->run($request))->toBe('simple flow2');

});
test('test flow state', function () {
    $flowManager = new FlowManager('state');
    $flowManager->configureStandalone()->resetState();
    $flowManager->register(\WpStarter\Flow\Simple::make('simple')->action(function($flow){
        if($flow->state('test')!='test'){
            $flow->state('test','test');
            return 'simple flow 1st time';
        }
        return 'simple flow 2nd time';
    }));
    $request=new \WpStarter\Flow\FlowRequest();
    expect($flowManager->run($request))->toBe('simple flow 1st time');
    expect($flowManager->run($request))->toBe('simple flow 2nd time');
});
test('test flow match multiple routes', function () {
    $flowManager = new FlowManager('route');
    $flowManager->configureStandalone();
    $flowManager->register(Simple::make('simple')->action(function(){
        return 'simple flow as default';
    }));
    $flowManager->register(Simple::make('login')->action(function(){
        return 'login flow';
    }));
    $flowManager->withRoute(function(){
        \WpStarter\Flow\Support\Facades\Route::match(['login','start'],'login');
    });

    $request=new \WpStarter\Flow\FlowRequest(['message'=>'login']);
    expect($flowManager->run($request))->toBe('login flow');
    $request=new \WpStarter\Flow\FlowRequest(['message'=>'start']);
    expect($flowManager->run($request))->toBe('login flow');
    $request=new \WpStarter\Flow\FlowRequest(['message'=>'not contains sta3rt or lo3gin','id'=>1]);
    expect($flowManager->run($request))->toBe('simple flow as default');

});
test('test flow provider', function () {
    $flowManager = new FlowManager('provider');
    $flowManager->configureStandalone();
    $flowManager->register(\WpStarter\Flow\Tests\Flow\TestFlowProvider::class);
    $request=new \WpStarter\Flow\FlowRequest(['id'=>1,'message'=>'login']);
    $response=$flowManager->run($request);
    expect($response)->toBe(['login']);
    $request=new \WpStarter\Flow\FlowRequest(['id'=>1,'message'=>'123']);
    $response=$flowManager->run($request);
    expect($response)->toBe(['order']);
    $request=new \WpStarter\Flow\FlowRequest(['id'=>1,'message'=>'hi']);
    $response=$flowManager->run($request);
    expect($response)->toBe(['order']);
    $flowManager->flows()->resetState();//Reset to default
    $request=new \WpStarter\Flow\FlowRequest(['id'=>1,'message'=>'hi']);
    $response=$flowManager->run($request);
    expect($response)->toBe(['login']);
    $request=new \WpStarter\Flow\FlowRequest(['id'=>1,'message'=>'simple']);
    $response=$flowManager->run($request);
    expect($response)->toBe('simple flow');
    $request=new \WpStarter\Flow\FlowRequest(['id'=>1,'message'=>'/order']);
    $response=$flowManager->run($request);
    expect($response)->toBe(['order']);
});
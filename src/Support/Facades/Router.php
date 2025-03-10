<?php

namespace WpStarter\Flow\Support\Facades;

use WpStarter\Flow\FlowRequest;

class Router extends Facade
{
    /**
     * Find a flow matched given the request
     * @param FlowRequest $request
     * @return
     */
    public static function match(FlowRequest $request){
        return static::getFlowManager()->router()->match($request);
    }
}
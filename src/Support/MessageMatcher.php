<?php

namespace WpStarter\Flow\Support;

use WpStarter\Flow\FlowRequest;
use WpStarter\Flow\FlowRoute;

class MessageMatcher extends Matcher
{

    function match(FlowRequest $request, FlowRoute $route): bool
    {
        $channelMatch=true;
        if ($route->channel) {
            if(is_array($route->channel)){
                $channelMatch=in_array($request->channel,$route->channel);
            }else {
                $channelMatch = stripos($route->channel, $request->channel) !== false;
            }
        }
        if ($message = $request->getMessage()) {
            $routes = is_array($route->route) ? $route->route : [$route->route];
            foreach ($routes as $kw) {
                $messageMatch = $kw && is_string($kw) && stripos($message, $kw) !== false;
                if ($channelMatch && $messageMatch) {
                    return true;
                }
            }
        }
        return false;
    }
}
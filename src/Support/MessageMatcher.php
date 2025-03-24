<?php

namespace WpStarter\Flow\Support;

use WpStarter\Flow\FlowRequest;

class MessageMatcher extends Matcher
{

    function match(FlowRequest $request, $value): bool
    {
        $route=$value['route'];
        $channel=$value['channel']??null;
        $channelMatch=true;
        if ($channel) {
            $channelMatch=stripos($channel,$request->channel)!==false;
        }
        if ($message = $request->getMessage()) {
            $routes = is_array($route) ? $route : [$route];
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
<?php

namespace WpStarter\Flow\Support;

use WpStarter\Flow\FlowRequest;

class MessageMatcher extends Matcher
{

    function match(FlowRequest $request, $value): bool
    {
        if ($message = $request->getMessage()) {
            $values = is_array($value) ? $value : [$value];
            foreach ($values as $kw) {
                if ($kw && is_string($kw) && stripos($message, $kw) !== false) {
                    return true;
                }
            }
        }
        return false;
    }
}
<?php

namespace WpStarter\Flow;

use WpStarter\Flow\Support\Helper;
use WpStarter\Flow\Support\Matcher;

class FlowRouter
{
    /**
     * @var FlowCollection
     */
    protected FlowCollection $flows;

    protected array $routes = [];
    /**
     * @var Matcher[]
     */
    protected array $matchers = [];

    public function __construct($matchers = [])
    {
        $this->matchers = is_array($matchers) ? $matchers : [$matchers];
    }

    public function flows(FlowCollection $flows): FlowRouter
    {
        $this->flows = $flows;
        return $this;
    }

    public function addMatcher(...$matchers): FlowRouter
    {
        $matchers = is_array($matchers[0]) ? $matchers[0] : $matchers;
        $this->matchers = array_merge($this->matchers, $matchers);
        return $this;
    }

    public function widthMatchers($matchers): FlowRouter
    {
        $this->matchers = $matchers;
        return $this;
    }

    public function add($route, $flow, $channel='public'): FlowRouter
    {
        $channel = Helper::getFlowChannel($channel);
        $flow = Helper::getFlowUniqueId($flow);
        if ($route && $flow) {
            $this->routes[] = compact('route', 'flow', 'channel');
        }
        return $this;
    }

    public function prepend($route, $flow, $channel='public'): FlowRouter
    {
        $channel = Helper::getFlowChannel($channel);
        $flow = Helper::getFlowUniqueId($flow);
        if ($route && $flow) {
            array_unshift($this->routes, compact('route', 'flow', 'channel'));
        }
        return $this;
    }

    public function match(FlowRequest $request)
    {
        $matchedFlow = null;
        foreach ($this->routes as $route) {
            if ($route['route'] instanceof \Closure) {
                if ($route['route']($request)) {
                    $matchedFlow = $route['flow'];
                    break;
                }
            } else {
                foreach ($this->matchers as $matcher) {
                    if ($matcher->match($request, $route)) {
                        $matchedFlow = $route['flow'];
                        break 2;
                    }
                }
            }
        }
        return $matchedFlow;
    }
}
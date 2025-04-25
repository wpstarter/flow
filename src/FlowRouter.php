<?php

namespace WpStarter\Flow;

use PHPUnit\TextUI\Help;
use WpStarter\Flow\Support\Helper;
use WpStarter\Flow\Support\Matcher;

class FlowRouter
{
    /**
     * @var FlowCollection
     */
    protected FlowCollection $flows;
    /**
     * @var FlowRoute[]
     */
    protected array $routes = [];
    /**
     * @var Matcher[]
     */
    protected array $matchers = [];
    /**
     * Global middlewares
     * @var array
     */
    protected array $middleware=[];

    public function __construct($matchers = [])
    {
        $this->matchers = is_array($matchers) ? $matchers : [$matchers];
    }

    public function flows(FlowCollection $flows): FlowRouter
    {
        $this->flows = $flows;
        return $this;
    }

    public function middleware(...$middleware): FlowRouter
    {
        $this->middleware = Helper::mergeMiddlewares($this->middleware, ...$middleware);
        return $this;
    }
    public function setMiddleware($middleware): FlowRouter
    {
        $this->middleware = $middleware;
        return $this;
    }
    public function getMiddleware(): array
    {
        return $this->middleware;
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

    public function add($route, $flow, $channel=null, $middleware=null, $prepend=false): FlowRouter
    {
        if(is_string($flow)){
            if(!$this->flows->find($flow)) {
                $this->flows->register($flow);
            }
            $flow=$this->flows->find($flow);
        }
        $channel = $channel ?? Helper::getFlowChannel($flow);
        $flowId = Helper::getFlowUniqueId($flow);
        if ($route && $flowId) {
            $route=new FlowRoute(['route'=>$route, 'flow'=>$flowId, 'channel'=>$channel, 'middleware'=>$middleware]);;
            if($prepend){
                array_unshift($this->routes, $route);
            }else {
                $this->routes[] = $route;
            }
        }
        return $this;
    }

    public function prepend($route, $flow, $channel=null, $middleware=null): FlowRouter
    {
        return $this->add($route, $flow, $channel, $middleware, true);
    }

    /**
     * @param FlowRequest $request
     * @return FlowRoute|null
     */
    public function match(FlowRequest $request)
    {
        $matchedRoute = null;
        foreach ($this->routes as $route) {
            if ($route['route'] instanceof \Closure) {
                if ($route['route']($request)) {
                    $matchedRoute = $route;
                    break;
                }
            } else {
                foreach ($this->matchers as $matcher) {
                    if ($matcher->match($request, $route)) {
                        $matchedRoute = $route;
                        break 2;
                    }
                }
            }
        }
        return $matchedRoute;
    }
}
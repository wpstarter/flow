<?php

namespace WpStarter\Flow;


use WpStarter\Flow\State\ArrayStore;
use WpStarter\Flow\State\FlowData;
use WpStarter\Flow\State\FlowRegistry;
use WpStarter\Flow\State\LaravelStore;
use WpStarter\Flow\State\StatelessFlow;
use WpStarter\Flow\State\WpStarterStore;
use WpStarter\Flow\Support\Facades\Facade;
use WpStarter\Flow\Support\FlowProvider;
use WpStarter\Flow\Support\Helper;
use WpStarter\Flow\Support\MessageMatcher;

class FlowManager
{
    protected static FlowManager $instance;
    /**
     * @var FlowCollection
     */
    protected FlowCollection $flows;
    protected FlowRouter $router;

    protected array $pendingFlows = [];
    protected array $routes = [];
    protected bool $resetState = false;

    public FlowRequest $request;

    protected string $managerId;

    public function __construct($managerId = '')
    {
        $this->managerId = $managerId;
        $this->router = new FlowRouter(new MessageMatcher());
        static::$instance = $this;
        Facade::setFlowManager($this);
    }

    public function configureStandalone()
    {
        FlowData::useStore(ArrayStore::class);
        FlowRegistry::useStore(ArrayStore::class);
        return $this;
    }

    public function configureLaravel()
    {
        FlowData::useStore(LaravelStore::class);
        FlowRegistry::useStore(LaravelStore::class);
        return $this;
    }

    public function configureWpStarter()
    {
        FlowData::useStore(WpStarterStore::class);
        FlowRegistry::useStore(WpStarterStore::class);
        return $this;
    }

    public function run(FlowRequest $request)
    {
        $this->request = $request;
        $requestId=$request->getIdentifier();
        $this->flows = FlowCollection::forSession($this->managerId?$this->managerId.':'.$requestId:$requestId);
        $this->router->flows($this->flows);
        $this->maybeResetState();
        $this->registerFlows($request);
        $this->registerRoutes();
        $matchedRoute = $this->router->match($request);
        if ($matchedRoute) {
            $flow=$this->flows->resolve($matchedRoute->flow);
            if(! $flow instanceof StatelessFlow){
                $this->flows->setState($matchedRoute->flow);
            }
        }else{
            $flow = $this->flows->current();
            $matchedRoute=new FlowRoute(['route'=>null, 'flow'=>null, 'channel'=>null, 'middleware'=>null]);
        }
        if ($flow) {
            try {
                $response=$matchedRoute->run($flow, $request);
            } catch (ResponseException $exception) {
                $response = $exception->getResponse();
            }
            return $response;
        }
        return null;
    }

    /**
     * Reset current state if requested
     */
    protected function maybeResetState()
    {
        if ($this->resetState) {
            $this->flows->resetState();
            $this->resetState = false;
        }
    }
    public function resetState()
    {
        $this->resetState = true;
        return $this;
    }

    public function register(...$flows)
    {
        $flows = is_array($flows[0]) ? $flows[0] : $flows;
        $this->pendingFlows = array_merge($this->pendingFlows, $flows);
        return $this;
    }

    public function withRoute($route)
    {
        $this->routes[] = $route;
        return $this;
    }

    protected function registerFlows($request)
    {
        $providers = [];
        foreach ($this->pendingFlows as $flow) {
            if ($flow instanceof \Closure) {
                $flow = $flow($request);
            }
            if (is_string($flow) && is_subclass_of($flow, FlowProvider::class)) {
                $flowProvider = new $flow($this);
                $flowProvider->register();
                $providers[] = $flowProvider;
                continue;
            }
            $this->flows->register($flow);
        }
        foreach ($providers as $provider) {
            $provider->boot();
        }
    }

    protected function registerRoutes()
    {
        foreach ($this->routes as $route) {
            if ($route instanceof \Closure) {
                $route($this->router);
            } elseif (file_exists($route)) {
                require_once $route;
            }
        }
        foreach ($this->flows as $flow) {
            if ($route=$flow->getRoute()) {
                $this->router->add($route, $flow);
            }
        }
    }

    public function flows()
    {
        return $this->flows;
    }

    /**
     * @return FlowRouter
     */
    public function router()
    {
        return $this->router;
    }

    /**
     * Registers a callback to execute when a change occurs and returns the FlowManager instance.
     *
     * @param callable $callback The callback function to execute on change.
     * @return FlowManager The current instance of the FlowManager.
     */
    function changed($callback): FlowManager
    {
        $this->flows()->changed($callback);
        return $this;
    }

    public static function instance()
    {
        if (!static::$instance) {
            static::$instance = new static();
        }
        return static::$instance;
    }
} 
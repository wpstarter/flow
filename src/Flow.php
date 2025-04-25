<?php

namespace WpStarter\Flow;

use Closure;
use RuntimeException;
use WpStarter\Flow\State\FlowData;
use WpStarter\Flow\Support\Helper;

abstract class Flow
{
    public $channel;
    protected $id;
    /**
     * @var string|array match key if specified
     */
    protected $route;
    /**
     * @var Closure
     */
    protected Closure $flowsResolver;
    protected FlowData $state;
    protected FlowRequest $request;
    protected string $sessionKey;
    protected array $middleware=[];

    public function __construct($id = null)
    {
        if ($id) {
            $this->id = $id;
        }
    }
    /**
     * @param $id
     * @return static
     */
    public static function make($id = null): Flow
    {
        return new static($id);
    }

    public function setSessionKey($sessionKey): Flow
    {
        $this->sessionKey = (string)$sessionKey;
        return $this;
    }

    function loadState(){
        if (!$this->sessionKey) {
            throw new RuntimeException('Session key not set for flow ' . get_class($this) . ($this->id ? ':' . $this->id : ''));
        }
        $this->state = static::createStorage($this, $this->sessionKey);
    }

    function getId()
    {
        return $this->id;
    }

    function dispatch(FlowRequest $request)
    {
        $this->request = $request;
        return $this->handle($request);
    }

    /**
     * @param $flow
     * @param $sessionKey
     * @return FlowData
     */
    static function createStorage($flow, $sessionKey): FlowData
    {
        $flowUid=Helper::getFlowUniqueId($flow);
        $key = $flowUid.':'. $sessionKey ;
        return new FlowData($key);
    }

    /**
     * Reset state of this flow
     */
    function resetState()
    {
        $this->state->reset();
    }

    function state($key, $value = null)
    {
        if (func_num_args() == 1) {
            return $this->state->get($key);
        }
        $this->state[$key] = $value;
        $this->state->save();
        return $this;
    }

    /**
     * Handle the flow request.
     * @param FlowRequest $request
     * @return mixed
     */
    abstract protected function handle(FlowRequest $request);

    /**
     * Set next flow
     * @param null $to
     * @param null $ttl
     * @return $this
     */
    function redirect($to, $ttl = null): Flow
    {
        $this->getFlows()->goTo($to, $ttl);
        return $this;
    }
    function redirectToDefault()
    {
        $this->getFlows()->resetState();
    }

    function back($default = null, $ttl = null)
    {
        $this->getFlows()->back($default, $ttl);
    }

    function dispatchTo($flow)
    {
        return $this->getFlows()->resolve($flow)->dispatch($this->request);
    }

    function setFlowsResolver($resolver): Flow
    {
        $this->flowsResolver = $resolver;
        return $this;
    }

    function getFlows() : FlowCollection
    {
        return call_user_func($this->flowsResolver, $this);
    }

    function isCurrent(): bool
    {
        return $this->getFlows()->currentFlowId() === get_class($this);
    }

    public function getRoute()
    {
        return $this->route;
    }
    public function getMiddleware(): array
    {
        return $this->middleware;
    }
} 
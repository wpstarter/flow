<?php

namespace WpStarter\Flow;

use RuntimeException;
use WpStarter\Flow\State\FlowData;
use WpStarter\Flow\Support\Helper;

abstract class Flow
{
    protected $id;
    /**
     * @var string|array match key if specified
     */
    protected $route;
    /**
     * @var FlowCollection|Flow[]
     */
    protected $flows;
    protected FlowData $state;
    protected FlowRequest $request;
    protected string $sessionKey;

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
        $flow = $this->flows->find($to);
        if ($flow) {
            $this->getFlows()->setState($flow, $ttl);
        } else {
            throw new RuntimeException('Flow ' . $to . ' not found');
        }
        return $this;
    }
    function redirectToDefault(){
        $this->getFlows()->resetState();
    }

    function back($default = null)
    {
        $this->getFlows()->back($default);
    }

    function dispatchTo($flow)
    {
        return $this->getFlows()->resolve($flow)->dispatch($this->request);
    }

    function setFlows(FlowCollection $collection): Flow
    {
        $this->flows = $collection;
        return $this;
    }

    function getFlows()
    {
        return $this->flows;
    }

    function isCurrent(): bool
    {
        return $this->getFlows()->currentFlowId() === get_class($this);
    }

    public function getRoute()
    {
        return $this->route;
    }
} 
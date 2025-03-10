<?php

namespace WpStarter\Flow;

use Traversable;
use WpStarter\Flow\State\FlowRegistry;
use WpStarter\Flow\Support\Helper;

class FlowCollection implements \IteratorAggregate
{
    /**
     * @var array|Flow[]
     */
    protected array $flows = [];
    protected array $aliases = [];
    protected FlowRegistry $registry;
    protected string $sessionKey;
    protected array $resolved = [];
    protected array $flowChangedCallbacks = [];
    protected string $default='';

    public function __construct($sessionKey)
    {
        $this->sessionKey = 'flows:' . $sessionKey;
        $this->registry = new FlowRegistry('flows_registry:' . $sessionKey);
    }

    static public function forSession($key): FlowCollection
    {
        return new static($key);
    }

    public function register(...$flows): FlowCollection
    {
        if (is_array($flows[0])) {
            $flows = [$flows[0]];
        }
        foreach ($flows as $flow) {
            if (empty($flow)) {
                continue;
            }
            if (is_string($flow)) {
                $flow = $this->make($flow);
            }
            if ($flow instanceof \Closure) {
                $flow = $flow();
            }
            if ($flow instanceof Flow) {
                $flow->setFlows($this);
                $flow->setSessionKey($this->sessionKey);
                $registerKey = Helper::getFlowUniqueId($flow);
                $duplicated = $this->flows[$registerKey] ?? null;
                if ($duplicated) {
                    $duplicated = get_class($duplicated);
                    throw new \RuntimeException("Flow with id {$registerKey} already registered as {$duplicated}");
                }
                $this->flows[$registerKey] = $flow;
                if ($id = $flow->getId()) {
                    $this->aliases[$id] = $registerKey;
                }
                if (!$this->default) {
                    $this->default = $registerKey;
                }

                if (method_exists($flow, 'boot')) {
                    $flow->boot();
                }
            } else {
                throw new \RuntimeException('Invalid flow need to be instance of ' . Flow::class . ' class. ' . get_class($flow) . ' passed');
            }
        }
        return $this;
    }



    public function getClassName($id)
    {
        $id = Helper::getFlowUniqueId($id);
        return $this->aliases[$id] ?? $id;
    }

    /**
     * @param $flow
     * @return Flow
     */
    public function make($flow): Flow
    {
        return Helper::app()->make($flow);
    }

    /**
     * Find a flow from registered
     * @param $idOrClass
     * @return Flow|null
     */
    public function find($idOrClass): ?Flow
    {
        $class = $this->getClassName($idOrClass);
        return $this->flows[$class] ?? null;
    }
    /**
     * Resolve a flow and load its state
     * @param null $id
     * @return Flow|null
     */
    public function resolve($id = null): ?Flow
    {
        if (!$id && !$id = $this->currentFlowId()) {
            $this->setState($this->default);
            $id = $this->currentFlowId();
        }
        $flow = $this->find($id);
        if ($flow) {
            $flow->loadState();
            return $flow;
        }
        if ($id) {
            throw new \InvalidArgumentException("Flow {$id} not found");
        } else {
            if (!$this->flows) {
                throw new \InvalidArgumentException("No flows set. Please register flows using register method.");
            } else {
                throw new \InvalidArgumentException("No default flow set. Please set a default flow using setDefault method.");
            }
        }
    }

    public function search($search)
    {
        //Search in alias
        foreach ($this->aliases as $alias => $class) {
            if (stripos($alias, $search) !== false) {
                return $class;
            }
        }
        foreach ($this->flows as $class => $flow) {
            if (stripos($class, $search) !== false) {
                return $class;
            }
        }
        throw new \InvalidArgumentException("Flow {$search} not found");
    }

    /**
     * @param $callback
     * @return Flow|null
     */
    function current($callback = null)
    {
        $current = $this->resolve();
        if ($callback && $current) {
            $callback($current);
        }
        return $current;
    }

    function setDefault($default)
    {
        $this->default = $default;
        return $this;
    }

    public function currentFlowId()
    {
        return $this->registry->currentFlowId();
    }

    public function setState($flow, $ttl = null): FlowCollection
    {
        if (!$flow) {
            $flow = $this->default;
        }
        $flow = $this->getClassName($flow);
        $currentFlow = $this->currentFlowId();
        $this->registry->push($flow, $ttl);
        if ($flow !== $currentFlow) {
            $this->flowChanged($currentFlow, $flow);
        }
        return $this;
    }

    public function resetState()
    {
        $this->setState(null);
    }

    public function changed($callback)
    {
        $this->flowChangedCallbacks[] = $callback;
        return $this;
    }

    protected function flowChanged($from, $to)
    {
        foreach ($this->flowChangedCallbacks as $callback) {
            $callback($from, $to);
        }
    }

    /**
     * Reset flow data
     * @param $flow
     */
    public function reset($flow)
    {
        $this->resolve($flow)->resetState();
    }

    /**
     * Change to flow and reset its data
     * @param $flow
     * @param null $ttl
     * @return FlowCollection
     */
    public function rewind($flow, $ttl = null): FlowCollection
    {
        $this->setState($flow, $ttl);
        $this->reset($flow);
        return $this;
    }

    public function back($default = null, $ttl = null)
    {
        $previousFlowID = $this->registry->previousFlowId();
        if (!$previousFlowID) {
            $previousFlowID = $default;
        }
        $this->setState($previousFlowID, $ttl);
        return $this;
    }

    /**
     * @return FlowRegistry
     */
    function registry()
    {
        return $this->registry;
    }

    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        return new \ArrayIterator($this->flows);
    }
}
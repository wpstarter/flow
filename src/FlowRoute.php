<?php

namespace WpStarter\Flow;

/**
 * @property string $route
 * @property string $flow
 * @property string $channel
 * @property array $middleware
 */
class FlowRoute implements \ArrayAccess
{
    /**
     * @var array
     * ['route'=>$route, 'flow'=>$flowId, 'channel'=>$channel, 'middleware'=>$middlewares]
     */
    protected $attributes;
    public function __construct($route)
    {
        $this->attributes=$route;
    }

    public function run(Flow $flow,FlowRequest $request){
        return $flow->dispatch($request);
    }
    /**
     * Determine if the given offset exists.
     *
     * @param  string  $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return isset($this->attributes[$offset]);
    }

    /**
     * Get the value at the given offset.
     *
     * @param  string  $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->attributes[$offset]??null;
    }

    /**
     * Set the value at the given offset.
     *
     * @param  string  $offset
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->attributes[] = $value;
        } else {
            $this->attributes[$offset] = $value;
        }
    }

    /**
     * Unset the value at the given offset.
     *
     * @param  string  $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->attributes[$offset]);
    }

    public function __get($name)
    {
        return $this->offsetGet($name);
    }

    public function __set($name, $value)
    {
        $this->offsetSet($name, $value);
    }

    public function __isset($name)
    {
        return $this->offsetExists($name);
    }

    public function __unset($name)
    {
        $this->offsetUnset($name);
    }


}

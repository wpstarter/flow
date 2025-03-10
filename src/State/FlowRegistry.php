<?php

namespace WpStarter\Flow\State;


class FlowRegistry
{
    use HasStore;


    protected static $maxPrevious = 10;

    public function __construct($id)
    {
        $this->store = new static::$storeClass($id);
    }

    public static function maxPrevious($max)
    {
        static::$maxPrevious = $max;
    }

    public static function useStore($store)
    {
        static::$storeClass = $store;
    }

    /**
     * Push to stack
     * @param $flow
     * @param $ttl
     * @return bool
     */
    public function push($flow, $ttl = null)
    {
        if (is_object($flow)) {
            $flow = get_class($flow);
        }
        $current = $this->store->get('current');
        if ($flow) {
            if ($flow === $current) {
                return false;
            }
            $previous = $this->store->get('previous');
            $previous[] = $current;
            if (count($previous) > static::$maxPrevious) {
                $previous = array_slice($previous, count($previous) - static::$maxPrevious);
            }
            $this->store->forever('previous', $previous);
        }
        $this->store->put('current', $flow, $ttl);
        return true;
    }

    public function currentFlowId()
    {
        return $this->store->get('current');
    }

    public function previousFlowId()
    {
        $previous = $this->store->get('previous');

        if (is_array($previous) && !empty($previous)) {
            return end($previous);
        }

        return null; // Return null if the stack is empty or not an array
    }

    public static function make($phone)
    {
        return new static($phone);
    }
} 
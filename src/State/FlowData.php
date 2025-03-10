<?php

namespace WpStarter\Flow\State;


class FlowData extends Fluent
{
    use HasStore;

    public function __construct($key)
    {
        $this->store = $this->makeStore($key);
        parent::__construct($this->store->get('data', []));
    }

    function reset()
    {
        $this->attributes = [];
    }

    function flush()
    {
        $this->store->forget('data');
    }

    function save()
    {
        $this->store->put('data', $this->attributes);
    }

    public function __destruct()
    {
        $this->save();
    }
} 
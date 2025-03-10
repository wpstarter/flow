<?php

namespace WpStarter\Flow;

use Closure;
use RuntimeException;
use WpStarter\Flow\Support\EvaluatesClosures;

/**
 * @method static Simple make($id)
 */
class Simple extends Flow
{
    use EvaluatesClosures;

    protected ?Closure $handler = null;

    public function __construct($id)
    {
        parent::__construct($id);
        $this->route = $this->id;
    }



    function handle(FlowRequest $request)
    {
        return $this->evaluate($this->handler, [
            'request' => $request,
            'flow' => $this,
        ], [
            FlowRequest::class => $request,
            Simple::class => $this,
        ]);
    }

    public function route($route): Simple
    {
        $this->route = $route;
        return $this;
    }

    public function action(Closure $action): Simple
    {
        $this->handler = $action;
        return $this;
    }

    public function getId()
    {
        $id = parent::getId();
        if (!$id) {
            throw new RuntimeException('Simple flow must have an id');
        }
        return $id;
    }
}
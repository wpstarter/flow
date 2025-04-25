<?php

namespace WpStarter\Flow\Support;

use WpStarter\Flow\FlowRequest;
use WpStarter\Flow\FlowRoute;

abstract class Matcher
{
    abstract function match(FlowRequest $request, FlowRoute $route) : bool;
}
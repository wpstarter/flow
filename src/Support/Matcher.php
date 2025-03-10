<?php

namespace WpStarter\Flow\Support;

use WpStarter\Flow\FlowRequest;

abstract class Matcher
{
    abstract function match(FlowRequest $request, $value) : bool;
}
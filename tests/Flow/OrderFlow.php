<?php

namespace WpStarter\Flow\Tests\Flow;

use WpStarter\Flow\Flow;

class OrderFlow extends Flow
{
    protected function handle(\WpStarter\Flow\FlowRequest $request){
        return ['order'];
    }
}
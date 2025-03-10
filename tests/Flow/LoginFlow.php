<?php

namespace WpStarter\Flow\Tests\Flow;

use WpStarter\Flow\Flow;

class LoginFlow extends Flow
{
    protected $route = 'login';
    protected function handle(\WpStarter\Flow\FlowRequest $request)
    {
        $this->redirect(OrderFlow::class);
        return ['login'];
    }
}
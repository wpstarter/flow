<?php

namespace WpStarter\Flow;

class ResponseException extends \Exception
{
    protected $response;

    public function __construct($response, $code = 0)
    {
        $this->response = $response;
        parent::__construct('response', $code);
    }

    public function getResponse()
    {
        return $this->response;
    }
}
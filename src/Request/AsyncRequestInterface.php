<?php

namespace SellsyApi\Request;

use GuzzleHttp\Promise\PromiseInterface;

interface AsyncRequestInterface extends RequestInterface {

    /**
     * @param string $method
     * @param mixed  $params
     *
     * @return PromiseInterface
     */
    public function callAsync ($method, $params);

}

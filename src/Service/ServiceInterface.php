<?php


namespace SellsyApi\Service;


use GuzzleHttp\Promise\PromiseInterface;
use SellsyApi\Request\AsyncRequestInterface;
use SellsyApi\Request\RequestInterface;

interface ServiceInterface {

    /**
     * @return RequestInterface
     */
    public function getRequest ();

    /**
     * @return AsyncRequestInterface
     */
    public function getAsyncRequest ();

    /**
     * @param string $method
     * @param mixed  $params
     *
     * @return mixed
     */
    public function call ($method, $params);

    /**
     * @param string $method
     * @param mixed  $params
     *
     * @return PromiseInterface
     */
    public function callAsync ($method, $params);

}

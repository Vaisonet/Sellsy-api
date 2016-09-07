<?php


namespace SellsyApi\Service;


use GuzzleHttp\Promise\PromiseInterface;
use Psr\Log\LoggerInterface;
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

    /**
     * @param callable $callback
     *
     * @return mixed
     */
    public function retryableCall(callable $callback);

    /**
     * @param callable $callback
     *
     * @return PromiseInterface
     */
    public function retryableCallAsync(callable $callback);

    /**
     * Set the request logger
     *
     * @param LoggerInterface $logger
     *
     * @return ServiceInterface
     */
    public function setLogger (LoggerInterface $logger);

    /**
     * Get the request logger
     *
     * @return LoggerInterface
     */
    public function getLogger ();

}

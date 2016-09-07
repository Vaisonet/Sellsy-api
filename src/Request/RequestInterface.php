<?php

namespace SellsyApi\Request;

use Psr\Log\LoggerInterface;

interface RequestInterface {

    /**
     * @param mixed $method
     * @param mixed $params
     *
     * @return mixed
     */
    public function call ($method, $params);

    /**
     * Set the request logger
     *
     * @param LoggerInterface $logger
     *
     * @return RequestInterface
     */
    public function setLogger (LoggerInterface $logger);

    /**
     * Get the request logger
     *
     * @return LoggerInterface
     */
    public function getLogger ();

}

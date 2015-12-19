<?php


namespace SellsyApi\Service;


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

}
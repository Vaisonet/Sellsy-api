<?php

namespace SellsyApi\Request;

interface RequestInterface {

    /**
     * @param mixed $method
     * @param mixed $params
     *
     * @return mixed
     */
    public function call ($method, $params);

}

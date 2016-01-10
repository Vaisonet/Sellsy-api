<?php

namespace SellsyApi;

use SellsyApi\Request\Request;
use SellsyApi\Service\GenericService;
use SellsyApi\Service\ServiceInterface;

class Client {

    /**
     * @var Request
     */
    protected $request;

    /**
     * Client constructor.
     *
     * @param array $config Array which must contains the userToken, userSecret, consumerToken and consumerSecret
     *                      generated on Sellsy's website
     */
    public function __construct (array $config) {

        if (!array_key_exists('userToken', $config)) {
            throw new \InvalidArgumentException('userToken is required');
        }
        if (!array_key_exists('userSecret', $config)) {
            throw new \InvalidArgumentException('userSecret is required');
        }
        if (!array_key_exists('consumerToken', $config)) {
            throw new \InvalidArgumentException('consumerToken is required');
        }
        if (!array_key_exists('consumerSecret', $config)) {
            throw new \InvalidArgumentException('consumerSecret is required');
        }

        $this->request = new Request($config['userToken'], $config['userSecret'], $config['consumerToken'],
                                     $config['consumerSecret']);
        $this->request->setEndPoint('https://apifeed.sellsy.com/0/');

    }

    /**
     * @param string $name
     *
     * @return ServiceInterface
     */
    public function getService ($name) {
        return new GenericService($this->request, $name);
    }

}

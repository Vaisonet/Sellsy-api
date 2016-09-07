<?php

namespace SellsyApi;

use Psr\Log\LoggerInterface;
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

        if (array_key_exists('logger', $config)) {
            $this->setLogger($config['logger']);
        }

    }

    /**
     * Set the request logger
     *
     * @param LoggerInterface $logger
     *
     * @return Client
     */
    public function setLogger (LoggerInterface $logger) {
        $this->request->setLogger($logger);
        return $this;
    }

    /**
     * Get the request logger
     *
     * @return LoggerInterface
     */
    public function getLogger () {
        return $this->request->getLogger();
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

<?php


namespace SellsyApi\Service;

use Psr\Log\LoggerInterface;
use SellsyApi\Request\AsyncRequestInterface;
use SellsyApi\Request\RequestInterface;

class GenericService implements ServiceInterface {

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var string
     */
    protected $name;

    /**
     * Service constructor.
     *
     * @param RequestInterface $request
     * @param string           $name
     */
    public function __construct (RequestInterface $request, $name) {
        $this->request = $request;
        $this->name    = $name;
    }

    /**
     * @return string
     */
    public function getName () {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function getRequest () {
        return $this->request;
    }

    /**
     * Set the request logger
     *
     * @param LoggerInterface $logger
     *
     * @return GenericService
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
     * @inheritdoc
     */
    public function getAsyncRequest () {
        if (!($this->request instanceof AsyncRequestInterface)) {
            throw new \RuntimeException('Can not use Async Request with this instance of this service');
        }
        return $this->request;
    }

    /**
     * @inheritdoc
     */
    public function call ($method, $params) {
        return $this->getRequest()->call($this->name . '.' . $method, $params);
    }

    /**
     * @inheritdoc
     */
    public function callAsync ($method, $params) {
        return $this->getAsyncRequest()->callAsync($this->name . '.' . $method, $params);
    }


    /**
     * @inheritdoc
     */
    public function retryableCall (callable $callback) {
        return $this->_retryCall('call', $callback);
    }

    /**
     * @inheritdoc
     */
    public function retryableCallAsync (callable $callback) {
        return $this->_retryCall('callAsync', $callback);
    }

    /**
     * @param string     $methodToCall
     * @param callable   $callback
     * @param int        $retry
     * @param \Exception $e
     *
     * @return mixed
     */
    protected function _retryCall ($methodToCall, callable $callback, $retry = 0, \Exception $e = NULL) {
        list($method, $params) = $callback($this, $retry, $e);
        try {
            return $this->$methodToCall($method, $params);
        } catch (\Exception $e) {
            return $this->_retryCall($methodToCall, $callback, $retry + 1, $e);
        }
    }


}

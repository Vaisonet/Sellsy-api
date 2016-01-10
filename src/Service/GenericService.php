<?php


namespace SellsyApi\Service;

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

}

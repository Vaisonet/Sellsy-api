<?php


namespace SellsyApi\Test\Service;


use GuzzleHttp\Promise\PromiseInterface;
use SellsyApi\Request\AsyncRequestInterface;
use SellsyApi\Request\RequestInterface;
use SellsyApi\Service\GenericService;

class GenericServiceTest extends \PHPUnit_Framework_TestCase {

    public function testConstructor () {
        /**
         * @var RequestInterface $mockRequest
         */
        $mockRequest = $this->getMock(RequestInterface::class);
        $service     = new GenericService($mockRequest, 'name');
        $this->assertInstanceOf(RequestInterface::class, $service->getRequest());
        $this->assertSame('name', $service->getName());
    }

    public function testGetAsyncRequest () {
        /**
         * @var AsyncRequestInterface $mockRequest
         */
        $mockRequest = $this->getMock(AsyncRequestInterface::class);
        $service     = new GenericService($mockRequest, 'name');
        $this->assertInstanceOf(AsyncRequestInterface::class, $service->getAsyncRequest());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testTeAsyncRequestException () {
        /**
         * @var RequestInterface $mockRequest
         */
        $mockRequest = $this->getMock(RequestInterface::class);
        $service     = new GenericService($mockRequest, 'name');
        $service->getAsyncRequest();
    }

    public function testCall () {
        /**
         * @var RequestInterface $mockRequest
         */
        $mockRequest = $this->getMock(RequestInterface::class);
        $mockRequest->expects($this->once())->method('call')
                    ->with($this->equalTo('name.method'), $this->equalTo(['field' => 'value']))->willReturn('result');
        $service = new GenericService($mockRequest, 'name');
        $res     = $service->call('method', ['field' => 'value']);
        $this->assertEquals('result', $res);

    }

    public function testCallAsync () {
        /**
         * @var AsyncRequestInterface $mockRequest
         */
        $mockPromise = $this->getMock(PromiseInterface::class);
        $mockRequest = $this->getMock(AsyncRequestInterface::class);
        $mockRequest->expects($this->once())->method('callAsync')
                    ->with($this->equalTo('name.method'), $this->equalTo(['field' => 'value']))
                    ->willReturn($mockPromise);
        $service = new GenericService($mockRequest, 'name');
        $res     = $service->callAsync('method', ['field' => 'value']);
        $this->assertSame($mockPromise, $res);

    }

}

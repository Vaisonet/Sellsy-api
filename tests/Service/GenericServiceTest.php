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

    public function testRetryableCall () {
        /**
         * @var RequestInterface $mockRequest
         */
        $mockRequest = $this->getMock(RequestInterface::class);
        $mockRequest->expects($this->once())->method('call')
                    ->with($this->equalTo('name.method'), $this->equalTo(['field' => 'value']))->willReturn('result');
        $service = new GenericService($mockRequest, 'name');
        $res     = $service->retryableCall(function () use ($service) {
            $args = func_get_args();
            $this->assertCount(3, $args);
            $this->assertSame($service, $args[0]);
            $this->assertSame(0, $args[1]);
            $this->assertNull($args[2]);
            return ['method', ['field' => 'value']];
        });
        $this->assertEquals('result', $res);

    }

    public function testRetryableCallAsync () {
        /**
         * @var AsyncRequestInterface $mockRequest
         */
        $mockPromise = $this->getMock(PromiseInterface::class);
        $mockRequest = $this->getMock(AsyncRequestInterface::class);
        $mockRequest->expects($this->once())->method('callAsync')
                    ->with($this->equalTo('name.method'), $this->equalTo(['field' => 'value']))
                    ->willReturn($mockPromise);
        $service = new GenericService($mockRequest, 'name');
        $res     = $service->retryableCallAsync(function () use ($service) {
            $args = func_get_args();
            $this->assertCount(3, $args);
            $this->assertSame($service, $args[0]);
            $this->assertSame(0, $args[1]);
            $this->assertNull($args[2]);
            return ['method', ['field' => 'value']];
        });
        $this->assertSame($mockPromise, $res);

    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Exception thrown by the function of the user
     */
    public function testErrorOnRetryableCall () {
        /**
         * @var RequestInterface $mockRequest
         */
        $mockRequest = $this->getMock(RequestInterface::class);
        $e           = new \Exception;
        $mockRequest->expects($this->exactly(3))->method('call')
                    ->with($this->equalTo('name.method'), $this->equalTo(['field' => 'value']))
                    ->will($this->throwException($e));
        $service = new GenericService($mockRequest, 'name');
        $retryNb  = 0;
        $service->retryableCall(function () use ($service, &$retryNb, $e) {
            $args = func_get_args();
            $this->assertCount(3, $args);
            $this->assertSame($service, $args[0]);
            $this->assertSame($retryNb, $args[1]);
            if ($retryNb) {
                $this->assertSame($e, $args[2]);
                if ($retryNb >= 3) {
                    throw new \Exception('Exception thrown by the function of the user');
                }
            } else {
                $this->assertNull($args[2]);
            }
            $retryNb++;
            return ['method', ['field' => 'value']];
        });

    }

}

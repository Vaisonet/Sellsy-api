<?php


namespace SellsyApi\Test\Request;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Response;
use SellsyApi\Request\Request;

class RequestTest extends \PHPUnit_Framework_TestCase {

    /**
     * @return Request
     */
    protected function createRequest () {
        return new Request('userToken', 'userSecret', 'consumerToken', 'consumerSecret');
    }

    /**
     * @param Request $request
     * @param string  $methodToCall
     * @param string  $method
     * @param mixed   $params
     * @param mixed   $return
     */
    protected function prepareRequest (Request $request, $methodToCall, $method, $params, $return) {
        $prop = new \ReflectionProperty($request, 'client');
        $prop->setAccessible(TRUE);
        $client = $this->createClientMock($methodToCall, $method, $params, $return);
        $prop->setValue($request, $client);
    }

    /**
     * @param string $methodToMock
     * @param string $methodName
     * @param mixed  $params
     * @param mixed  $result
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function createClientMock ($methodToMock, $methodName, $params, $result) {
        $client = $this->getMock(Client::class, [$methodToMock], [['base_uri' => 'http://api.sellsy.com']]);
        $client->expects($this->once())->method($methodToMock)->willReturnCallback(function ($method, $options) use (
            $methodName, $params, $result
        ) {
            $this->assertEmpty($method);
            $this->assertInternalType('array', $options);

            $this->assertArrayHasKey('headers', $options);
            $headers = $options['headers'];
            $this->assertInternalType('array', $headers);
            $this->assertArrayHasKey('Authorization', $headers);
            $this->assertArrayHasKey('Expect', $headers);

            $this->assertArrayHasKey('multipart', $options);
            $doIn = ['method' => $methodName, 'params' => $params];
            $this->assertEquals([['name' => 'request', 'contents' => '1'],
                                 ['name' => 'io_mode', 'contents' => 'json'],
                                 ['name' => 'do_in', 'contents' => json_encode($doIn),],
                                ], $options['multipart']);

            $this->assertArrayHasKey('verify', $options);

            return $result;
        });
        return $client;
    }

    public function testEndPoint () {
        $request = $this->createRequest();
        $this->assertNull($request->getEndPoint());
        $this->assertSame($request, $request->setEndPoint('endPoint'));
        $this->assertEquals('endPoint', $request->getEndPoint());
        $request = new Request('', '', '', '', 'endPointBis');
        $this->assertSame('endPointBis', $request->getEndPoint());
    }

    public function testCall () {
        $request = $this->createRequest();
        $prop    = new \ReflectionProperty($request, 'client');
        $prop->setAccessible(TRUE);
        $params = ['field' => 'value'];
        $method = 'method';
        $client = $this->createClientMock('post', $method, $params,
                                          new Response(200, [], '{"status":"success","response":"result"}'));
        $prop->setValue($request, $client);
        $this->assertEquals('result', $request->call($method, $params));
    }

    public function testCallAsync () {
        $params  = ['field' => 'value'];
        $method  = 'method';
        $promise = new Promise();
        $request = $this->createRequest();
        $this->prepareRequest($request, 'postAsync', $method, $params, $promise);
        $res = $request->callAsync($method, $params);
        $this->assertInstanceOf(PromiseInterface::class, $res);
        $promise->resolve(new Response(200, [], '{"status":"success","response":"result"}'));
        $this->assertEquals('result', $res->wait());
    }

    public function testCallAsyncWithRequestError () {
        $promise = new Promise();
        $request = $this->createRequest();
        $this->prepareRequest($request, 'postAsync', '', '', $promise);
        $res = $request->callAsync('', '');
        $this->assertInstanceOf(PromiseInterface::class, $res);
        $promise->reject($this->getMockBuilder(RequestException::class)->disableOriginalConstructor()->getMock());
        $otherwiseCount = 0;
        $res->then(function () {
            throw new \PHPUnit_Framework_ExpectationFailedException('Fullfilled callback was not expected to be called.');
        }, function ($e) use (&$otherwiseCount) {
            ++$otherwiseCount;
            return $e;
        });
        try {
            $res->wait();
        } catch (RequestException $e) {
        }
        if ($otherwiseCount != 1) {
            throw new \PHPUnit_Framework_ExpectationFailedException('OnRejected callback was expected to be called once.');
        }
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testCallWithoutEndPoint () {
        $request = $this->createRequest();
        $request->call('', []);
    }

    public function testCallWithoutClientInitialize () {
        $request = $this->createRequest();
        $request->setEndPoint('http://github.com');
        $prop = new \ReflectionProperty($request, 'client');
        $prop->setAccessible(TRUE);
        $this->assertNull($prop->getValue($request));
        try {
            $request->call('test', [])->wait();
        } catch (\Exception $e) {
        }
        $this->assertInstanceOf(Client::class, $prop->getValue($request));
    }

    /**
     * TODO : check that the request is not sent
     */
    public function testCancelPromise () {
        $request = $this->createRequest();
        $request->setEndPoint('http://github.com');
        $promise = $request->callAsync('method', []);
        try {
            $promise->cancel();
            $promise->wait();
        } catch (\Exception $e){

        }
        $this->assertSame($promise::REJECTED, $promise->getState());
    }

    public function testErrorResponse() {

    }

}
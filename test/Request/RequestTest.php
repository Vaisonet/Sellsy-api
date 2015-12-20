<?php


namespace SellsyApi\Test\Request;


use GuzzleHttp\Client;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Response;
use SellsyApi\Request\Request;

class RequestTest extends \PHPUnit_Framework_TestCase {

    public function testEndPoint () {
        $request = $this->createRequest();
        $this->assertNull($request->getEndPoint());
        $this->assertSame($request, $request->setEndPoint('endPoint'));
        $this->assertEquals('endPoint', $request->getEndPoint());
    }

    /**
     * @return Request
     */
    protected function createRequest () {
        return new Request('userToken', 'userSecret', 'consumerToken', 'consumerSecret');
    }

    public function testCall () {
        $request = $this->createRequest();
        $prop    = new \ReflectionProperty($request, 'client');
        $prop->setAccessible(TRUE);
        $params = ['field' => 'value'];
        $method = 'method';
        $client = $this->createClientMock('post', $method, $params, '{"status":"success","response":"result"}');
        $prop->setValue($request, $client);
        $this->assertEquals('result', $request->call($method, $params));
    }

    public function testCallAsync () {
        $request = $this->createRequest();
        $prop    = new \ReflectionProperty($request, 'client');
        $prop->setAccessible(TRUE);
        $params  = ['field' => 'value'];
        $method  = 'method';
        $promise = new Promise();
        $client  = $this->createClientMock('postAsync', $method, $params, $promise);
        $prop->setValue($request, $client);
        $res = $request->callAsync($method, $params);
        $this->assertInstanceOf(PromiseInterface::class, $res);
        $promise->resolve(new Response(200, [], '{"status":"success","response":"result"}'));
        $this->assertEquals('result', $res->wait());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function createClientMock ($methodToMock, $methodName, $params, $result) {
        $client = $this->getMock(Client::class, [$methodToMock]);
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

}
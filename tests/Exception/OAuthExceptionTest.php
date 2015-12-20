<?php


namespace SellsyApi\Test\Exception;


use SellsyApi\Exception\OAuthException;

class OAuthExceptionTest extends \PHPUnit_Framework_TestCase {

    public function testConstructor () {
        $prevE   = new \Exception();
        $code    = 123;
        $message = 'message';
        $e       = new OAuthException($message, $code, $prevE);

        $this->assertInstanceOf(OAuthException::class, $e);
        $this->assertSame($prevE, $e->getPrevious());
        $this->assertSame($code, $e->getCode());
        $this->assertSame($message, $e->getMessage());
    }

}
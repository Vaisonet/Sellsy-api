<?php


namespace SellsyApi\Test\Exception;


use SellsyApi\Exception\SellsyError;

class SellsyErrorTest extends \PHPUnit_Framework_TestCase {

    public function testConstructor () {
        $prevE   = new \Exception();
        $code    = 'E_CUSTOM';
        $message = 'message';
        $more    = 'more';
        $e       = new SellsyError($message, $code, $more, $prevE);

        $this->assertInstanceOf(SellsyError::class, $e);
        $this->assertSame($prevE, $e->getPrevious());
        $this->assertSame($code, $e->getSellsyCode());
        $this->assertSame($message, $e->getMessage());
        $this->assertSame($more, $e->getMore());
    }

}
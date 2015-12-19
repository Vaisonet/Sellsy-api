<?php


namespace SellsyApi\Test;


use SellsyApi\Client;

class ClientTest extends \PHPUnit_Framework_TestCase {

    public function testConstructor () {
        $client = new Client(['userToken'      => 'xxx',
                              'userSecret'     => 'xxx',
                              'consumerToken'  => 'xxx',
                              'consumerSecret' => 'xxx',
                             ]);
        $this->assertInstanceOf(Client::class, $client);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructorWithoutAllRequiredParameters () {
        new Client([]);
    }


}
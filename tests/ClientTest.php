<?php


namespace SellsyApi\Test;


use Psr\Log\LoggerInterface;
use SellsyApi\Client;
use SellsyApi\Service\ServiceInterface;

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
    public function testConstructorWithoutAllRequiredParametersUserToken () {
        new Client([]);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructorWithoutAllRequiredParametersUserSecret () {
        new Client(['userToken' => 'xxx',]);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructorWithoutAllRequiredParametersConsumerToken () {
        new Client(['userToken'  => 'xxx',
                    'userSecret' => 'xxx',
                   ]);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructorWithoutAllRequiredParametersConsumerSecret () {
        new Client(['userToken'     => 'xxx',
                    'userSecret'    => 'xxx',
                    'consumerToken' => 'xxx',
                   ]);
    }

    public function testGetService () {
        $client  = $this->createClient();
        $service = $client->getService('name');
        $this->assertInstanceOf(ServiceInterface::class, $service);
    }

    public function testGetLogger () {
        $client  = $this->createClient();
        $this->assertNull($client->getLogger());
        $logger = $this->getMock(LoggerInterface::class);
        $client->setLogger($logger);
        $this->assertSame($logger, $client->getLogger());
    }

    /**
     * @return Client
     */
    protected function createClient () {
        return new Client(['userToken'      => 'xxx',
                           'userSecret'     => 'xxx',
                           'consumerToken'  => 'xxx',
                           'consumerSecret' => 'xxx',
                          ]);
    }


}

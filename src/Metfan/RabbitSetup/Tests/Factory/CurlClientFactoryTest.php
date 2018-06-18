<?php
namespace Metfan\RabbitSetup\Tests\Factory;

use Metfan\RabbitSetup\Factory\CurlClientFactory;
use PHPUnit\Framework\TestCase;

/**
 * Unit test of Metfan\RabbitSetup\Factory\CurlClientFactory
 *
 * @author Ulrich
 * @package Metfan\RabbitSetup\Tests\Factory
 */
class CurlClientFactoryTest extends TestCase
{
    public function test()
    {
        $factory  = new CurlClientFactory();
        $client = $factory->createClient([
            'host' => '127.0.0.1',
            'port' => 15672,
            'user' => 'guest',
            'password' => 'guest',
        ]);

        $this->assertInstanceOf('Metfan\RabbitSetup\Http\CurlClient', $client);
        $this->assertAttributeEquals('127.0.0.1', 'host', $client);
        $this->assertAttributeEquals(15672, 'port', $client);
        $this->assertAttributeEquals('guest', 'user', $client);
        $this->assertAttributeEquals('guest', 'password', $client);
    }
}

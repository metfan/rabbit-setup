<?php
namespace Metfan\RabbitSetup\Tests\Container;

use Metfan\RabbitSetup\Container\HttpClientProvider;
use Pimple\Container;


/**
 * Unit test of Metfan\RabbitSetup\Container\HttpClientProvider
 *
 * @author Ulrich
 * @package Metfan\RabbitSetup\Tests\Container
 */
class HttpClientProviderTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        $container = new Container();
        $container->register(new HttpClientProvider());

        $this->assertInstanceOf('Metfan\RabbitSetup\Factory\CurlClientFactory', $container['curl_client_factory']);
        $this->assertInstanceOf('Metfan\RabbitSetup\Http\ClientPool', $container['http_client_pool']);

    }
}

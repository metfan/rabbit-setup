<?php
namespace Metfan\RabbitSetup\Container;

use Metfan\RabbitSetup\Factory\CurlClientFactory;
use Metfan\RabbitSetup\Http\ClientPool;
use Pimple\Container;
use Pimple\ServiceProviderInterface;


/**
 * Add definition of CurlClientFactory et ClientPool to container
 *
 * @author Ulrich
 * @package Metfan\RabbitSetup\Container
 */
class HttpClientProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container['curl_client_factory'] = function($c) {
            return new CurlClientFactory();
        };

        $container['http_client_pool'] = function($c) {
            return new ClientPool($c['curl_client_factory']);
        };
    }
}

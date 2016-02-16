<?php
namespace Metfan\RabbitSetup\Container;

use Metfan\RabbitSetup\Manager\RabbitMq\ExchangeManager;
use Metfan\RabbitSetup\Manager\RabbitMq\ParameterManager;
use Metfan\RabbitSetup\Manager\RabbitMq\PolicyManager;
use Metfan\RabbitSetup\Manager\RabbitMq\QueueManager;
use Metfan\RabbitSetup\Manager\RabbitMq\VhostManager;
use Pimple\Container;
use Pimple\ServiceProviderInterface;


/**
 * Add rabbitmq manager class to container
 *
 * @author Ulrich
 * @package Metfan\RabbitSetup\Container
 */
class RabbitMQManagerProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container['manager_rabbitmq_vhost'] = function($c) {
            return new VhostManager(
                $c['http_client_pool'],
                $c['manager_rabbitmq_parameter'],
                $c['manager_rabbitmq_exchange'],
                $c['manager_rabbitmq_queue'],
                $c['manager_rabbitmq_policy'],
                $c['logger']);
        };

        $container['manager_rabbitmq_exchange'] = function($c) {
            return new ExchangeManager($c['logger']);
        };

        $container['manager_rabbitmq_queue'] = function($c) {
            return new QueueManager($c['logger']);
        };

        $container['manager_rabbitmq_policy'] = function($c) {
            return new PolicyManager($c['logger']);
        };

        $container['manager_rabbitmq_parameter'] = function($c) {
            return new ParameterManager($c['logger']);
        };
    }
}
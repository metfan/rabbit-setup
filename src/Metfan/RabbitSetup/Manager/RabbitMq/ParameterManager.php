<?php
namespace Metfan\RabbitSetup\Manager\RabbitMq;

use Metfan\RabbitSetup\Http\ClientInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Parameter manager, only create parameter in RabbitMQ, about list or delete use rabbitmqctl.
 *
 * @author Ulrich
 * @package Metfan\RabbitSetup\Manager\RabbitMq
 */
class ParameterManager implements VhostAwareInterface, ClientAwareInterface
{
    use LoggerAwareTrait;
    use VhostAwareTrait;
    use ClientAwareTrait;

    /**
     * @param LoggerInterface|null $logger
     */
    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = (null === $logger) ? new NullLogger() : $logger;
    }

    /**
     * Create a RabbitMQ's parameter, hum ok only federation-upstream and federation-upstream-set are supported
     *
     * @param $paramType
     * @param $name
     * @param array $options
     */
    public function create($paramType, $name, array $options)
    {
        if ('federation-upstream' != $paramType && 'federation-upstream-set' != $paramType) {
            $this->logger->critical(sprintf('ParameterType: %s is not supported. Only federation-upstream & federation-upstream-set', $paramType));
            return;
        }

        $this->client->query(
            ClientInterface::METHOD_PUT,
            sprintf('/api/parameters/%s/%s/%s', $paramType, $this->vhost, $name),
            ['value' => $options]);

        $this->logger->info(sprintf('Create parameter type: <info>%s</info> and name: <info>%s</info>', $paramType, $name));
    }
}
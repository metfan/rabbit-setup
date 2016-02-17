<?php
namespace Metfan\RabbitSetup\Manager\RabbitMq;

use Metfan\RabbitSetup\Http\ClientInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * base manager
 *
 * @author Ulrich
 * @package Metfan\RabbitSetup\Manager
 */
abstract class BaseManager implements RabbitMQManagerInterface
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

    protected function findAllElements($url, $vhost)
    {
        if ($vhost) {
            $url = sprintf('%s/%s', $url, $vhost);
        }

        return json_decode($this->client->query(ClientInterface::METHOD_GET, $url), true);
    }

    protected function deleteElement($url, $vhost, $name, $elementType)
    {
        $response = $this->client->query(ClientInterface::METHOD_DELETE, sprintf('%s/%s/%s', $url, $vhost, $name));

        $this->logger->info(
            sprintf('Delete %s <info>%s</info> from vhost <info>%s</info>', $elementType, $name, urldecode($vhost)));

        return $response;
    }
}

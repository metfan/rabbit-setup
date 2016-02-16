<?php
namespace Metfan\RabbitSetup\Manager\RabbitMq;

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
}
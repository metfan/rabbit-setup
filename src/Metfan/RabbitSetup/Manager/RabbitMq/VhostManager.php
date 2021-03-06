<?php
namespace Metfan\RabbitSetup\Manager\RabbitMq;

use Metfan\RabbitSetup\Http\ClientInterface;
use Metfan\RabbitSetup\Http\ClientPool;
use Psr\Log\LoggerInterface;

/**
 * Manager in charge of create Exchange and manage exchange, queue under this  vhost
 *
 * @author Ulrich
 * @package Metfan\RabbitSetup\Manager
 */
class VhostManager
{
    /**
     * @var ParameterManager
     */
    private $parameterManager;

    /**
     * @var ExchangeManager
     */
    private $exchangeManager;

    /**
     * @var QueueManager
     */
    private $queueManager;

    /**
     * @var ClientPool
     */
    private $clientPool;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var PolicyManager
     */
    private $policyManager;

    /**
     * @param ClientPool $clientPool
     * @param ParameterManager $parameterManager
     * @param ExchangeManager $exchangeManager
     * @param QueueManager $queueManager
     * @param PolicyManager $policyManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        ClientPool $clientPool,
        ParameterManager $parameterManager,
        ExchangeManager $exchangeManager,
        QueueManager $queueManager,
        PolicyManager $policyManager,
        LoggerInterface $logger)
    {

        $this->parameterManager = $parameterManager;
        $this->exchangeManager = $exchangeManager;
        $this->queueManager = $queueManager;
        $this->clientPool = $clientPool;
        $this->logger = $logger;
        $this->policyManager = $policyManager;
    }

    /**
     * check $configuration, get client related to connection config, create vhost, then process exchange and queue
     * @param $vhostName
     * @param array $configuration
     */
    public function processVhost($vhostName, array $configuration)
    {
        $client = $this->clientPool->getClientByName($configuration['connection']);

        $vhostName = urlencode($vhostName);

        $client->query(
            ClientInterface::METHOD_PUT,
            sprintf('/api/vhosts/%s', $vhostName),
            []);
        $this->logger->info(sprintf('Create vhost: <info>%s</info>', urldecode($vhostName)));

        //add permissions of connection user to vhost
        $user = $this->clientPool->getUserByConnectionName($configuration['connection']);
        $client->query(
            ClientInterface::METHOD_PUT,
            sprintf('/api/permissions/%s/%s', $vhostName, urlencode($user)),
            ["configure" => ".*", "write" => ".*", "read" => ".*"]);
        $this->logger->info(
            sprintf(
                'Add permissions to vhost: <info>%s</info> for user: <info>%s</info>',
                urldecode($vhostName),
                $user
            )
        );

        //process parameters
        if (isset($configuration['parameters']) && count($configuration['parameters'])) {

            $this->parameterManager
                ->setClient($client)
                ->setVhost($vhostName);
            foreach ($configuration['parameters'] as $paramType => $parameters) {
                foreach ($parameters as $paramName => $paramOptions) {
                    $this->parameterManager->create($paramType, $paramName, $paramOptions);
                }
            }
        }

        //process exchanges
        if (isset($configuration['exchanges']) && count($configuration['exchanges'])) {
            $this->process($this->exchangeManager, $client, $vhostName, $configuration['exchanges']);
        }

        //process queues
        if (isset($configuration['queues']) && count($configuration['queues'])) {
            $this->process($this->queueManager, $client, $vhostName, $configuration['queues']);
        }

        //process policies
        if (isset($configuration['policies']) && count($configuration['policies'])) {
            $this->process($this->policyManager, $client, $vhostName, $configuration['policies']);
        }
    }

    private function process(RabbitMQManagerInterface $manager, ClientInterface $client, $vhostName, $elements)
    {
        $manager
            ->setClient($client)
            ->setVhost($vhostName);
        foreach ($elements as $name => $element) {
            $manager->create($name, $element);
        }
    }
}

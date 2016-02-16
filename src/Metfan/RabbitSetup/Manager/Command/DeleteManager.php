<?php
namespace Metfan\RabbitSetup\Manager\Command;

use Metfan\RabbitSetup\Manager\RabbitMq\ExchangeManager;
use Metfan\RabbitSetup\Manager\RabbitMq\PolicyManager;
use Metfan\RabbitSetup\Manager\RabbitMq\QueueManager;
use Psr\Log\LoggerInterface;


/**
 * Manager of rsetup:delete Command
 *
 * @author Ulrich
 * @package Metfan\RabbitSetup\Manager\Command
 */
class DeleteManager
{
    /**
     * @var ExchangeManager
     */
    private $exchangeManager;

    /**
     * @var QueueManager
     */
    private $queueManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var PolicyManager
     */
    private $policyManager;

    public function __construct(
        ExchangeManager $exchangeManager,
        QueueManager $queueManager,
        PolicyManager $policyManager,
        LoggerInterface $logger)
    {
        $this->exchangeManager = $exchangeManager;
        $this->queueManager = $queueManager;
        $this->policyManager = $policyManager;
        $this->logger = $logger;
    }

    /**
     * Delete selected queues.
     * If queues == "*" get list of queue from rabbitmq API
     *
     * @param $vhost
     * @param $queues
     */
    public function deleteQueues($vhost, $queues)
    {
        if ('*' === $queues) {
            //so you want to destroy all queues, as you whish, let me time to list' em all
            $queues = $this->queueManager->getAll(urlencode($vhost));
        } elseif (!$vhost) {
            throw new \InvalidArgumentException('If you want to delete certain queue(s) you have to provide vhost');
        } else {
            //few queues to delete, let stack them in array
            $queues = explode(',', $queues);
            array_walk($queues, function(&$item, $key, $vhost){
                $item = ['vhost' => $vhost, 'name' => trim($item)];
            }, $vhost);
        }

        if (0 == count($queues)) {
            $this->logger->error('No queue found.');
            return;
        }

        foreach ($queues as $queue) {
            $this->queueManager->delete(urlencode($queue['vhost']), $queue['name']);
        }
    }

    /**
     * Delete selected exchanges.
     * If exchanges == "*" get list of exchange from rabbitmq API
     *
     * @param $vhost
     * @param $exchanges
     */
    public function deleteExchanges($vhost, $exchanges)
    {
        if ('*' === $exchanges) {
            //so you want to destroy all exchanges, as you whish, let me time to list'em all
            $exchanges = $this->exchangeManager->getAll(urlencode($vhost));
            foreach ($exchanges as $key => $exchange) {
                if (0 === strpos($exchange['name'], 'amq') || !$exchange['name']) {
                    unset($exchanges[$key]);
                }
            }
        } elseif (!$vhost) {
            throw new \InvalidArgumentException('If you want to delete certain exchange(s) you have to provide vhost');
        } else {
            //few exchanges to delete, let stack them in array
            $exchanges = explode(',', $exchanges);
            array_walk($exchanges, function(&$item, $key, $vhost){
                $item = ['vhost' => $vhost, 'name' => trim($item)];
            }, $vhost);
        }

        if (0 == count($exchanges)) {
            $this->logger->error('No exchange found.');
            return;
        }

        foreach ($exchanges as $exchange) {
            if (false === strpos($exchange['name'], 'amq') && $exchange['name']) {
                $this->exchangeManager->delete(urlencode($exchange['vhost']), $exchange['name']);
            }
        }
    }

    public function deletePolicies($vhost, $policies)
    {
        if ('*' === $policies) {
            //so you want to destroy all policies, as you whish, let me time to list'em all
            $policies = $this->policyManager->getAll(urlencode($vhost));
        } elseif (!$vhost) {
            throw new \InvalidArgumentException('If you want to delete certain policie(s) you have to provide vhost');
        } else {
            //few policies to delete, let stack them in array
            $policies = explode(',', $policies);
            array_walk($policies, function(&$item, $key, $vhost){
                $item = ['vhost' => $vhost, 'name' => trim($item)];
            }, $vhost);
        }

        if (0 == count($policies)) {
            $this->logger->error('No policy found.');
            return;
        }

        foreach ($policies as $policy) {
            $this->policyManager->delete(urlencode($policy['vhost']), $policy['name']);
        }
    }
}
<?php
namespace Metfan\RabbitSetup\Tests\Manager\RabbitMq;

use Metfan\RabbitSetup\Http\ClientInterface;
use Metfan\RabbitSetup\Manager\RabbitMq\VhostManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test of Metfan\RabbitSetup\Manager\RabbitMq\VhostManager
 *
 * @author Ulrich
 * @package Metfan\RabbitSetup\Tests\Manager\RabbitMq
 */
class VhostManagerTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $parameterManager;

    /**
     * @var MockObject
     */
    private $exchangeManager;

    /**
     * @var MockObject
     */
    private $queueManager;

    /**
     * @var MockObject
     */
    private $clientPool;

    /**
     * @var MockObject
     */
    private $client;

    /**
     * @var MockObject
     */
    private $logger;

    /**
     * @var MockObject
     */
    private $policyManager;

    protected function setUp()
    {
        parent::setUp();

        $this->parameterManager = $this->getMockBuilder('Metfan\RabbitSetup\Manager\RabbitMq\ParameterManager')->getMock();
        $this->exchangeManager = $this->getMockBuilder('Metfan\RabbitSetup\Manager\RabbitMq\ExchangeManager')->getMock();
        $this->queueManager = $this->getMockBuilder('Metfan\RabbitSetup\Manager\RabbitMq\QueueManager')->getMock();
        $this->clientPool = $this->getMockBuilder('Metfan\RabbitSetup\Http\ClientPool')->disableOriginalConstructor()->getMock();
        $this->client = $this->getMockBuilder('Metfan\RabbitSetup\Http\ClientInterface')->getMock();
        $this->logger = $this->getMockBuilder('Psr\Log\LoggerInterface')->getMock();
        $this->policyManager = $this->getMockBuilder('Metfan\RabbitSetup\Manager\RabbitMq\PolicyManager')->getMock();
    }

    private function createVhost()
    {
        $manager = new VhostManager(
            $this->clientPool,
            $this->parameterManager,
            $this->exchangeManager,
            $this->queueManager,
            $this->policyManager,
            $this->logger);

        $this->clientPool
            ->expects($this->once())
            ->method('getClientByName')
            ->willReturn($this->client);

        $this->client
            ->expects($this->at(0))
            ->method('query')
            ->with(
                ClientInterface::METHOD_PUT,
                '/api/vhosts/%2F',
                []
            );

        $this->logger
            ->expects($this->at(0))
            ->method('info')
            ->with('Create vhost: <info>/</info>');

        $this->clientPool
            ->expects($this->once())
            ->method('getUserByConnectionName')
            ->willReturn('guest');

        $this->client
            ->expects($this->at(1))
            ->method('query')
            ->with(
                ClientInterface::METHOD_PUT,
                '/api/permissions/%2F/guest',
                ["configure" => ".*", "write" => ".*", "read" => ".*"]
            );

        $this->logger
            ->expects($this->at(1))
            ->method('info')
            ->with('Add permissions to vhost: <info>/</info> for user: <info>guest</info>');

        return $manager;
    }

    public function testParameters()
    {
        $manager = $this->createVhost();

        $this->parameterManager
            ->expects($this->once())
            ->method('setClient')
            ->with($this->client)
            ->willReturnSelf();

        $this->parameterManager
            ->expects($this->once())
            ->method('setVhost')
            ->with('%2F')
            ->willReturnSelf();

        $this->parameterManager
            ->expects($this->once())
            ->method('create')
            ->with('federation-upstream', 'thor', []);

        $manager->processVhost('/', ['connection' => [], 'parameters' => ['federation-upstream' => ['thor' => []]]]);
    }

    public function testExchanges()
    {
        $manager = $this->createVhost();

        $this->exchangeManager
            ->expects($this->once())
            ->method('setClient')
            ->with($this->client)
            ->willReturnSelf();

        $this->exchangeManager
            ->expects($this->once())
            ->method('setVhost')
            ->with('%2F')
            ->willReturnSelf();

        $this->exchangeManager
            ->expects($this->once())
            ->method('create')
            ->with('unroutable', ['type' => 'fanout']);

        $manager->processVhost('/', ['connection' => [], 'exchanges' => ['unroutable' => ['type' => 'fanout']]]);
    }

    public function testQueues()
    {
        $manager = $this->createVhost();

        $this->queueManager
            ->expects($this->once())
            ->method('setClient')
            ->with($this->client)
            ->willReturnSelf();

        $this->queueManager
            ->expects($this->once())
            ->method('setVhost')
            ->with('%2F')
            ->willReturnSelf();

        $this->queueManager
            ->expects($this->once())
            ->method('create')
            ->with('unroutable', ['bindings' => ['exchange' => 'unroutable', 'routing_key' => null]]);

        $manager->processVhost('/', ['connection' => [], 'queues' => ['unroutable' => ['bindings' => ['exchange' => 'unroutable', 'routing_key' => null]]]]);
    }

    public function testPolicies()
    {
        $manager = $this->createVhost();

        $this->policyManager
            ->expects($this->once())
            ->method('setClient')
            ->with($this->client)
            ->willReturnSelf();

        $this->policyManager
            ->expects($this->once())
            ->method('setVhost')
            ->with('%2F')
            ->willReturnSelf();

        $this->policyManager
            ->expects($this->once())
            ->method('create')
            ->with('FEDEXCHANGE', ['pattern' => '^rmq_']);

        $manager->processVhost('/', ['connection' => [], 'policies' => ['FEDEXCHANGE' => ['pattern' => '^rmq_']]]);
    }
}

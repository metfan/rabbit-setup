<?php
namespace Metfan\RabbitSetup\Tests\Manager\RabbitMq;

use Metfan\RabbitSetup\Http\ClientInterface;
use Metfan\RabbitSetup\Manager\RabbitMq\VhostManager;

/**
 * Unit test of Metfan\RabbitSetup\Manager\RabbitMq\VhostManager
 *
 * @author Ulrich
 * @package Metfan\RabbitSetup\Tests\Manager\RabbitMq
 */
class VhostManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $parameterManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $exchangeManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $queueManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $clientPool;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $client;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $policyManager;

    protected function setUp()
    {
        parent::setUp();

        $this->parameterManager = $this->getMock('Metfan\RabbitSetup\Manager\RabbitMq\ParameterManager');
        $this->exchangeManager = $this->getMock('Metfan\RabbitSetup\Manager\RabbitMq\ExchangeManager');
        $this->queueManager = $this->getMock('Metfan\RabbitSetup\Manager\RabbitMq\QueueManager');
        $this->clientPool = $this->getMockBuilder('Metfan\RabbitSetup\Http\ClientPool')->disableOriginalConstructor()->getMock();
        $this->client = $this->getMock('Metfan\RabbitSetup\Http\ClientInterface');
        $this->logger = $this->getMock('Psr\Log\LoggerInterface');
        $this->policyManager = $this->getMock('Metfan\RabbitSetup\Manager\RabbitMq\PolicyManager');
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
            ->expects($this->once())
            ->method('query')
            ->with(
                ClientInterface::METHOD_PUT,
                '/api/vhosts/%2F',
                []
            );

        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with('Create vhost: <info>/</info>');

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

<?php
namespace Metfan\RabbitSetup\Tests\Manager\Command;

use Metfan\RabbitSetup\Manager\Command\DeleteManager;
use Metfan\RabbitSetup\Manager\RabbitMq\ExchangeManager;
use Metfan\RabbitSetup\Manager\RabbitMq\PolicyManager;
use Metfan\RabbitSetup\Manager\RabbitMq\QueueManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Unit test of Metfan\RabbitSetup\Manager\Command\DeleteManager
 *
 * @author Ulrich
 * @package Metfan\RabbitSetup\Tests\Manager\Command
 */
class DeleteManagerTest extends TestCase
{
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
    private $logger;

    /**
     * @var MockObject
     */
    private $policyManager;

    protected function setUp()
    {
        parent::setUp();

        $this->exchangeManager = $this->getMockBuilder(ExchangeManager::class)->getMock();
        $this->queueManager = $this->getMockBuilder(QueueManager::class)->getMock();
        $this->policyManager = $this->getMockBuilder(PolicyManager::class)->getMock();
        $this->logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
    }

    public function testDeleteQueueMissingVhost()
    {
        $manager = new DeleteManager($this->exchangeManager, $this->queueManager, $this->policyManager, $this->logger);

        $this->expectException(\InvalidArgumentException::class);
        $manager->deleteQueues(null, 'test');
    }

    public function testDeleteQueueNotFound()
    {
        $manager = new DeleteManager($this->exchangeManager, $this->queueManager, $this->policyManager, $this->logger);

        $this->queueManager
            ->expects($this->once())
            ->method('getAll')
            ->with('%2F')
            ->willReturn([]);

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with('No queue found.');

        $manager->deleteQueues('/', '*');
    }

    public function testDeleteQueue()
    {
        $manager = new DeleteManager($this->exchangeManager, $this->queueManager, $this->policyManager, $this->logger);

        $this->queueManager
            ->expects($this->at(0))
            ->method('delete')
            ->with('%2F', 'test1');

        $this->queueManager
            ->expects($this->at(1))
            ->method('delete')
            ->with('%2F', 'test2');

        $manager->deleteQueues('/', 'test1,test2');
    }

    public function testDeleteExchangeMissingVhost()
    {
        $manager = new DeleteManager($this->exchangeManager, $this->queueManager, $this->policyManager, $this->logger);

        $this->expectException(\InvalidArgumentException::class);
        $manager->deleteExchanges(null, 'test');
    }

    public function testDeleteExchangeNotFound()
    {
        $manager = new DeleteManager($this->exchangeManager, $this->queueManager, $this->policyManager, $this->logger);

        $this->exchangeManager
            ->expects($this->once())
            ->method('getAll')
            ->with('%2F')
            ->willReturn([]);

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with('No exchange found.');

        $manager->deleteExchanges('/', '*');
    }

    public function testDeleteExchangeFoundAmqp()
    {
        $manager = new DeleteManager($this->exchangeManager, $this->queueManager, $this->policyManager, $this->logger);

        $this->exchangeManager
            ->expects($this->once())
            ->method('getAll')
            ->with('%2F')
            ->willReturn([['name' => 'amq.direct']]);

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with('No exchange found.');

        $manager->deleteExchanges('/', '*');
    }

    public function testDeleteExchange()
    {
        $manager = new DeleteManager($this->exchangeManager, $this->queueManager, $this->policyManager, $this->logger);

        $this->exchangeManager
            ->expects($this->at(0))
            ->method('delete')
            ->with('%2F', 'test1');

        $this->exchangeManager
            ->expects($this->at(1))
            ->method('delete')
            ->with('%2F', 'test2');

        $manager->deleteExchanges('/', 'test1,test2');
    }


    public function testDeletePolicyMissingVhost()
    {
        $manager = new DeleteManager($this->exchangeManager, $this->queueManager, $this->policyManager, $this->logger);

        $this->expectException(\InvalidArgumentException::class);
        $manager->deletePolicies(null, 'test');
    }

    public function testDeletePolicyNotFound()
    {
        $manager = new DeleteManager($this->exchangeManager, $this->queueManager, $this->policyManager, $this->logger);

        $this->policyManager
            ->expects($this->once())
            ->method('getAll')
            ->with('%2F')
            ->willReturn([]);

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with('No policy found.');

        $manager->deletePolicies('/', '*');
    }

    public function testDeletePolicy()
    {
        $manager = new DeleteManager($this->exchangeManager, $this->queueManager, $this->policyManager, $this->logger);

        $this->policyManager
            ->expects($this->at(0))
            ->method('delete')
            ->with('%2F', 'test1');

        $this->policyManager
            ->expects($this->at(1))
            ->method('delete')
            ->with('%2F', 'test2');

        $manager->deletePolicies('/', 'test1,test2');
    }
}

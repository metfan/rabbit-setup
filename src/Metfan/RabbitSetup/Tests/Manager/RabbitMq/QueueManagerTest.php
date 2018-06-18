<?php
namespace Metfan\RabbitSetup\Tests\Manager\RabbitMq;

use Metfan\RabbitSetup\Http\ClientInterface;
use Metfan\RabbitSetup\Manager\RabbitMq\QueueManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test of Metfan\RabbitSetup\Manager\RabbitMq\QueueManager
 *
 * @author Ulrich
 * @package Metfan\RabbitSetup\Tests\Manager\RabbitMq
 */
class QueueManagerTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $logger;

    /**
     * @var MockObject
     */
    private $client;

    protected function setUp()
    {
        parent::setUp();

        $this->logger = $this->getMockBuilder('Psr\Log\LoggerInterface')->getMock();
        $this->client = $this->getMockBuilder('Metfan\RabbitSetup\Http\ClientInterface')->getMock();
    }


    public function providerCreate()
    {
        $arguments = ['x-message-ttl' => 3600];
        $binding = [
            ['exchange' => 'qTest', 'routing_key' => 'my_rk']
        ];

        return [
            [['durability' => 'durable', 'bindings' => []], ['durability' => 'durable']],
            [['durability' => 'durable', 'arguments' => [], 'bindings' => []], ['durability' => 'durable']],
            [['durability' => 'durable', 'arguments' => $arguments, 'bindings' => []], ['durability' => 'durable', 'arguments' => $arguments]],
            [['durability' => 'durable', 'bindings' => $binding], ['durability' => 'durable']],
        ];
    }

    /**
     * @dataProvider providerCreate
     * @param $options
     * @param $expectedOptions
     */
    public function testCreate($options, $expectedOptions)
    {
        $manager = new QueueManager();
        $this->assertAttributeInstanceOf('Psr\Log\NullLogger', 'logger', $manager);

        $manager->setLogger($this->logger);
        $this->assertAttributeEquals($this->logger, 'logger', $manager);

        $manager->setClient($this->client);
        $this->assertAttributeEquals($this->client, 'client', $manager);

        $manager->setVhost('test');

        $nbr = 0;
        
        $this->client
            ->expects($this->at($nbr))
            ->method('query')
            ->with(
                ClientInterface::METHOD_PUT,
                '/api/queues/test/qTest',
                $expectedOptions
            );

        $this->logger
            ->expects($this->at($nbr))
            ->method('info')
            ->with('Create queue: <info>qTest</info>');
        
        foreach ($options['bindings'] as $binding) {
            $nbr++;
            
            $this->client
                ->expects($this->at($nbr))
                ->method('query')
                ->with(
                    ClientInterface::METHOD_POST,
                    '/api/bindings/test/e/'.$binding['exchange'].'/q/qTest',
                    ['routing_key' => $binding['routing_key']]
                );

            $this->logger
                ->expects($this->at($nbr))
                ->method('info')
                ->with('Bind exchange <info>'.$binding['exchange'].'</info> with queue <info>qTest</info> using routing key <info>'.$binding['routing_key'].'</info>');
        }
        

        $manager->create('qTest', $options);
    }

    public function providerGetAll()
    {
        return [
            [null, '/api/queues'],
            ['test', '/api/queues/test'],
        ];
    }

    /**
     * @dataProvider providerGetAll
     * @param $vhost
     * @param $expectedUrl
     */
    public function testGetAll($vhost, $expectedUrl)
    {
        $manager = new QueueManager();
        $manager->setClient($this->client);

        $this->client
            ->expects($this->once())
            ->method('query')
            ->with(ClientInterface::METHOD_GET, $expectedUrl)
            ->willReturn(json_encode(['queues' => 'test']));

        $this->assertEquals(['queues' => 'test'], $manager->getAll($vhost));
    }

    public function testDelete()
    {
        $manager = new QueueManager();
        $manager->setClient($this->client);
        $manager->setLogger($this->logger);

        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with('Delete queue <info>qTest</info> from vhost <info>test</info>');

        $this->client
            ->expects($this->once())
            ->method('query')
            ->with(ClientInterface::METHOD_DELETE, '/api/queues/test/qTest')
            ->willReturn(true);

        $this->assertEquals(true, $manager->delete('test', 'qTest'));
    }
}

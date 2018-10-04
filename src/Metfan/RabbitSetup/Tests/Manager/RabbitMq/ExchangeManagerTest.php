<?php
namespace Metfan\RabbitSetup\Tests\Manager\RabbitMq;

use Metfan\RabbitSetup\Http\ClientInterface;
use Metfan\RabbitSetup\Manager\RabbitMq\ExchangeManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Unit test of Metfan\RabbitSetup\Manager\RabbitMq\ExchangeManager
 *
 * @author Ulrich
 * @package Metfan\RabbitSetup\Tests\Manager\RabbitMq
 */
class ExchangeManagerTest extends TestCase
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

        $this->logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $this->client = $this->getMockBuilder(ClientInterface::class)->getMock();
    }


    public function providerCreate()
    {
        return [
            [['type' => 'fanout'], ['type' => 'fanout']],
            [['type' => 'fanout', 'arguments' => []], ['type' => 'fanout']],
            [['type' => 'fanout', 'arguments' => ['durability' => 'durabe']], ['type' => 'fanout', 'arguments' => ['durability' => 'durabe']]],
        ];
    }

    /**
     * @dataProvider providerCreate
     * @param $options
     * @param $expectedOptions
     */
    public function testCreate($options, $expectedOptions)
    {
        $manager = new ExchangeManager();
        $this->assertAttributeInstanceOf(NullLogger::class, 'logger', $manager);

        $manager->setLogger($this->logger);
        $this->assertAttributeEquals($this->logger, 'logger', $manager);

        $manager->setClient($this->client);
        $this->assertAttributeEquals($this->client, 'client', $manager);

        $manager->setVhost('test');

        $this->client
            ->expects($this->once())
            ->method('query')
            ->with(
                ClientInterface::METHOD_PUT,
                '/api/exchanges/test/exTest',
                $expectedOptions
            );

        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with('Create exchange: <info>exTest</info>');

        $manager->create('exTest', $options);
    }

    public function providerGetAll()
    {
        return [
            [null, '/api/exchanges'],
            ['test', '/api/exchanges/test'],
        ];
    }

    /**
     * @dataProvider providerGetAll
     * @param $vhost
     * @param $expectedUrl
     */
    public function testGetAll($vhost, $expectedUrl)
    {
        $manager = new ExchangeManager();
        $manager->setClient($this->client);

        $this->client
            ->expects($this->once())
            ->method('query')
            ->with(ClientInterface::METHOD_GET, $expectedUrl)
            ->willReturn(json_encode(['exchanges' => 'test']));

        $this->assertEquals(['exchanges' => 'test'], $manager->getAll($vhost));
    }

    public function testDelete()
    {
        $manager = new ExchangeManager();
        $manager->setClient($this->client);
        $manager->setLogger($this->logger);

        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with('Delete exchange <info>exTest</info> from vhost <info>test</info>');

        $this->client
            ->expects($this->once())
            ->method('query')
            ->with(ClientInterface::METHOD_DELETE, '/api/exchanges/test/exTest')
            ->willReturn(true);

        $this->assertEquals(true, $manager->delete('test', 'exTest'));
    }
}

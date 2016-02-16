<?php
namespace Metfan\RabbitSetup\Tests\Manager\RabbitMq;

use Metfan\RabbitSetup\Http\ClientInterface;
use Metfan\RabbitSetup\Manager\RabbitMq\PolicyManager;

/**
 * Unit test of Metfan\RabbitSetup\Manager\RabbitMq\PolicyManager
 *
 * @author Ulrich
 * @package Metfan\RabbitSetup\Tests\Manager\RabbitMq
 */
class PolicyManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $client;

    protected function setUp()
    {
        parent::setUp();

        $this->logger = $this->getMock('Psr\Log\LoggerInterface');
        $this->client = $this->getMock('Metfan\RabbitSetup\Http\ClientInterface');
    }

    public function testCreate()
    {
        $manager = new PolicyManager();
        $this->assertAttributeInstanceOf('Psr\Log\NullLogger', 'logger', $manager);

        $manager->setLogger($this->logger);
        $manager->setClient($this->client);
        $manager->setVhost('test');

        $this->client
            ->expects($this->once())
            ->method('query')
            ->with(
                ClientInterface::METHOD_PUT,
                '/api/policies/test/exTest',
                ['pattern' => '^rmq_*']
            );

        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with('Create poilicy: <info>exTest</info>');

        $manager->create('exTest', ['pattern' => '^rmq_*']);
    }

    public function providerGetAll()
    {
        return [
            [null, '/api/policies'],
            ['test', '/api/policies/test'],
        ];
    }

    /**
     * @dataProvider providerGetAll
     * @param $vhost
     * @param $expectedUrl
     */
    public function testGetAll($vhost, $expectedUrl)
    {
        $manager = new PolicyManager();
        $manager->setClient($this->client);

        $this->client
            ->expects($this->once())
            ->method('query')
            ->with(ClientInterface::METHOD_GET, $expectedUrl)
            ->willReturn(json_encode(['policies' => 'test']));

        $this->assertEquals(['policies' => 'test'], $manager->getAll($vhost));
    }

    public function testDelete()
    {
        $manager = new PolicyManager();
        $manager->setClient($this->client);
        $manager->setLogger($this->logger);

        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with('Delete policy <info>exTest</info> from vhost <info>test</info>');

        $this->client
            ->expects($this->once())
            ->method('query')
            ->with(ClientInterface::METHOD_DELETE, '/api/policies/test/exTest')
            ->willReturn(true);

        $this->assertEquals(true, $manager->delete('test', 'exTest'));
    }
}

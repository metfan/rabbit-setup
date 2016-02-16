<?php
namespace Metfan\RabbitSetup\Tests\Manager\RabbitMq;

use Metfan\RabbitSetup\Http\ClientInterface;
use Metfan\RabbitSetup\Manager\RabbitMq\ParameterManager;

/**
 * Unit test of Metfan\RabbitSetup\Manager\RabbitMq\ParameterManager
 *
 * @author Ulrich
 * @package Metfan\RabbitSetup\Tests\Manager\RabbitMq
 */
class ParameterManagerTest extends \PHPUnit_Framework_TestCase
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


    public function providerCreate()
    {
        return [
            ['federation-upstream'],
            ['federation-upstream-set'],
        ];
    }

    /**
     * @dataProvider providerCreate
     * @param $paramType
     */
    public function testCreate($paramType)
    {
        $manager = new ParameterManager();
        $this->assertAttributeInstanceOf('Psr\Log\NullLogger', 'logger', $manager);

        $manager->setLogger($this->logger);
        $manager->setClient($this->client);
        $manager->setVhost('test');

        $this->client
            ->expects($this->once())
            ->method('query')
            ->with(
                ClientInterface::METHOD_PUT,
                '/api/parameters/'.$paramType.'/test/fedex',
                ['value' => ['upstream' => 'thor']]
            );

        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with('Create parameter type: <info>'.$paramType.'</info> and name: <info>fedex</info>');

        $manager->create($paramType, 'fedex', ['upstream' => 'thor']);
    }

    public function testCreateUnsupportedType()
    {
        $manager = new ParameterManager();
        $this->assertAttributeInstanceOf('Psr\Log\NullLogger', 'logger', $manager);

        $manager->setLogger($this->logger);

        $this->logger
            ->expects($this->once())
            ->method('critical')
            ->with('ParameterType: hole is not supported. Only federation-upstream & federation-upstream-set');

        $manager->create('hole', 'fedex', []);
    }
}

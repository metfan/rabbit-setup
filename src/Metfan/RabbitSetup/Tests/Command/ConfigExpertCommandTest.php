<?php
namespace Metfan\RabbitSetup\Tests\Command;

use Metfan\RabbitSetup\Command\ConfigExpertCommand;
use Metfan\RabbitSetup\Command\DeleteCommand;
use Metfan\RabbitSetup\Container\HttpClientProvider;
use Metfan\RabbitSetup\Container\RabbitMQManagerProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Pimple\Container;
use Symfony\Component\Console\Tester\CommandTester;


/**
 * Unit test of Metfan\RabbitSetup\Command\ConfigExpertCommand
 *
 * @author Ulrich
 * @package Metfan\RabbitSetup\Tests\Command
 */
class ConfigExpertCommandTest extends TestCase
{
    /**
     * @var DeleteCommand
     */
    private $command;

    /**
     * @var MockObject
     */
    private $client;

    protected function setUp()
    {
        parent::setUp();

        $this->client = $this->getMockBuilder('Metfan\RabbitSetup\Http\CurlClient')
            ->disableOriginalConstructor()
            ->getMock();
        $clientFactory = $this->getMockBuilder('Metfan\RabbitSetup\Factory\CurlClientFactory')->getMock();

        $clientFactory
            ->expects($this->any())
            ->method('createClient')
            ->willReturn($this->client);

        $container = new Container();
        $container->register(new HttpClientProvider());
        $container->register(new RabbitMQManagerProvider());
        $container['curl_client_factory'] = $clientFactory;

        $this->command = new ConfigExpertCommand($container);

        if (!defined('ROOT_PATH'))
            define('ROOT_PATH', realpath(__DIR__.'/../'));
    }

    public function testFailedParsing()
    {
        $tester = new CommandTester($this->command);
        $tester->execute(['configFile' => 'Parser/fixture']);

        $this->assertRegExp('#\[critical\] Le fichier de config est inutilisable: .*#', $tester->getDisplay(true));
        $this->assertEquals(1, $tester->getStatusCode());
    }

    public function testBasic()
    {
        $this->client
            ->expects($this->at(0))
            ->method('query')
            ->with('PUT', '/api/vhosts/%2F', []);

        $this->client
            ->expects($this->at(1))
            ->method('query')
            ->with('PUT', '/api/permissions/%2F/guest', ["configure" => ".*", "write" => ".*", "read" => ".*"]);

        $tester = new CommandTester($this->command);
        $tester->execute(['configFile' => 'Parser/fixture/config.yml']);

        $this->assertEquals("<>[info] Create vhost: /\n<>[info] Add permissions to vhost: / for user: guest\n", $tester->getDisplay(true));
        $this->assertEquals(0, $tester->getStatusCode());
    }
}

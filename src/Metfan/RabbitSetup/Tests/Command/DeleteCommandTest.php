<?php
namespace Metfan\RabbitSetup\Tests\Command;

use Metfan\RabbitSetup\Command\DeleteCommand;
use Metfan\RabbitSetup\Container\HttpClientProvider;
use Metfan\RabbitSetup\Container\RabbitMQManagerProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Pimple\Container;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Unit (or functional?) test of Metfan\RabbitSetup\Command\DeleteCommand
 *
 * @author Ulrich
 * @package Metfan\RabbitSetup\Tests\Command
 */
class DeleteCommandTest extends TestCase
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

        $this->command = new DeleteCommand($container);
    }

    public function testDeleteAllQueueWithVhost()
    {
        $this->client
            ->expects($this->at(0))
            ->method('query')
            ->with('GET', '/api/queues/%2F')
            ->willReturn(json_encode([
                ['vhost' => '/', 'name' => 'start']
            ]));

        $this->client
            ->expects($this->at(1))
            ->method('query')
            ->with('DELETE', '/api/queues/%2F/start');

        $tester = new CommandTester($this->command);
        $tester->execute(['--vhost' => '/', '--queues' => '*']);

        $this->assertEquals("<>[info] Delete queue start from vhost /\n", $tester->getDisplay(true));
        $this->assertEquals(0, $tester->getStatusCode());
    }

    public function testDeleteAllQueueWithoutVhost()
    {
        $this->client
            ->expects($this->at(0))
            ->method('query')
            ->with('GET', '/api/queues')
            ->willReturn(json_encode([
                ['vhost' => '/', 'name' => 'start'],
                ['vhost' => 'mail', 'name' => 'send']
            ]));

        $this->client
            ->expects($this->at(1))
            ->method('query')
            ->with('DELETE', '/api/queues/%2F/start');

        $this->client
            ->expects($this->at(2))
            ->method('query')
            ->with('DELETE', '/api/queues/mail/send');

        $tester = new CommandTester($this->command);
        $tester->execute(['--queues' => '*']);

        $this->assertEquals("<>[info] Delete queue start from vhost /\n<>[info] Delete queue send from vhost mail\n", $tester->getDisplay(true));
        $this->assertEquals(0, $tester->getStatusCode());
    }

    public function testDeleteNamedQueue()
    {
        $this->client
            ->expects($this->at(0))
            ->method('query')
            ->with('DELETE', '/api/queues/%2F/start');

        $this->client
            ->expects($this->at(1))
            ->method('query')
            ->with('DELETE', '/api/queues/%2F/send');

        $tester = new CommandTester($this->command);
        $tester->execute(['-VH' => '/', '-Q' => 'start,send']);

        $this->assertEquals("<>[info] Delete queue start from vhost /\n<>[info] Delete queue send from vhost /\n", $tester->getDisplay(true));
        $this->assertEquals(0, $tester->getStatusCode());
    }

    public function testDeleteAllExchangeWithVhost()
    {
        $this->client
            ->expects($this->at(0))
            ->method('query')
            ->with('GET', '/api/exchanges/%2F')
            ->willReturn(json_encode([
                ['vhost' => '/', 'name' => 'start'],
                ['vhost' => '/', 'name' => 'amq.direct']
            ]));

        $this->client
            ->expects($this->at(1))
            ->method('query')
            ->with('DELETE', '/api/exchanges/%2F/start');

        $tester = new CommandTester($this->command);
        $tester->execute(['--vhost' => '/', '--exchanges' => '*']);

        $this->assertEquals("<>[info] Delete exchange start from vhost /\n", $tester->getDisplay(true));
        $this->assertEquals(0, $tester->getStatusCode());
    }

    public function testDeleteAllExchangeWithoutVhost()
    {
        $this->client
            ->expects($this->at(0))
            ->method('query')
            ->with('GET', '/api/exchanges')
            ->willReturn(json_encode([
                ['vhost' => '/', 'name' => 'start'],
                ['vhost' => 'mail', 'name' => 'send']
            ]));

        $this->client
            ->expects($this->at(1))
            ->method('query')
            ->with('DELETE', '/api/exchanges/%2F/start');

        $this->client
            ->expects($this->at(2))
            ->method('query')
            ->with('DELETE', '/api/exchanges/mail/send');

        $tester = new CommandTester($this->command);
        $tester->execute(['--exchanges' => '*']);

        $this->assertEquals("<>[info] Delete exchange start from vhost /\n<>[info] Delete exchange send from vhost mail\n", $tester->getDisplay(true));
        $this->assertEquals(0, $tester->getStatusCode());
    }

    public function testDeleteNamedExchange()
    {
        $this->client
            ->expects($this->at(0))
            ->method('query')
            ->with('DELETE', '/api/exchanges/%2F/start');

        $this->client
            ->expects($this->at(1))
            ->method('query')
            ->with('DELETE', '/api/exchanges/%2F/send');

        $tester = new CommandTester($this->command);
        $tester->execute(['-VH' => '/', '-E' => 'start,send,amq.direct']);

        $this->assertEquals("<>[info] Delete exchange start from vhost /\n<>[info] Delete exchange send from vhost /\n", $tester->getDisplay(true));
        $this->assertEquals(0, $tester->getStatusCode());
    }

    public function testDeleteAllPolicyWithVhost()
    {
        $this->client
            ->expects($this->at(0))
            ->method('query')
            ->with('GET', '/api/policies/%2F')
            ->willReturn(json_encode([
                ['vhost' => '/', 'name' => 'start']
            ]));

        $this->client
            ->expects($this->at(1))
            ->method('query')
            ->with('DELETE', '/api/policies/%2F/start');

        $tester = new CommandTester($this->command);
        $tester->execute(['--vhost' => '/', '--policies' => '*']);

        $this->assertEquals("<>[info] Delete policy start from vhost /\n", $tester->getDisplay(true));
        $this->assertEquals(0, $tester->getStatusCode());
    }

    public function testDeleteAllPolicyWithoutVhost()
    {
        $this->client
            ->expects($this->at(0))
            ->method('query')
            ->with('GET', '/api/policies')
            ->willReturn(json_encode([
                ['vhost' => '/', 'name' => 'start'],
                ['vhost' => 'mail', 'name' => 'send']
            ]));

        $this->client
            ->expects($this->at(1))
            ->method('query')
            ->with('DELETE', '/api/policies/%2F/start');

        $this->client
            ->expects($this->at(2))
            ->method('query')
            ->with('DELETE', '/api/policies/mail/send');

        $tester = new CommandTester($this->command);
        $tester->execute(['--policies' => '*']);

        $this->assertEquals("<>[info] Delete policy start from vhost /\n<>[info] Delete policy send from vhost mail\n", $tester->getDisplay(true));
        $this->assertEquals(0, $tester->getStatusCode());
    }

    public function testDeleteNamedPolicy()
    {
        $this->client
            ->expects($this->at(0))
            ->method('query')
            ->with('DELETE', '/api/policies/%2F/start');

        $this->client
            ->expects($this->at(1))
            ->method('query')
            ->with('DELETE', '/api/policies/%2F/send');

        $tester = new CommandTester($this->command);
        $tester->execute(['-VH' => '/', '-P' => 'start,send']);

        $this->assertEquals("<>[info] Delete policy start from vhost /\n<>[info] Delete policy send from vhost /\n", $tester->getDisplay(true));
        $this->assertEquals(0, $tester->getStatusCode());
    }

    public function testGetException()
    {
        $this->client
            ->expects($this->once())
            ->method('query')
            ->with('DELETE', '/api/policies/%2F/start')
            ->willThrowException(new \RuntimeException('No network available'));

        $tester = new CommandTester($this->command);
        $tester->execute(['-VH' => '/', '-P' => 'start,send']);

        $this->assertEquals("[critical] No network available\n", $tester->getDisplay(true));
        $this->assertEquals(1, $tester->getStatusCode());
    }
}

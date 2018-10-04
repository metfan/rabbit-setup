<?php
namespace Metfan\RabbitSetup\Tests\Http;
<<<<<<< HEAD

=======
use Metfan\RabbitSetup\Factory\CurlClientFactory;
>>>>>>> fix test with new phpunit api
use Metfan\RabbitSetup\Http\ClientPool;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;


/**
 *
 *
 * @author Ulrich
 * @package Metfan\RabbitSetup\Tests\Http
 */
class ClientPoolTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $factory;

    protected function setUp()
    {
        parent::setUp();

        $this->factory = $this->getMockBuilder(CurlClientFactory::class)->getMock();
    }

    public function testUnknownName()
    {
        $pool = new ClientPool($this->factory);

        $this->expectException(\OutOfRangeException::class);
        $pool->getClientByName('ulrich');
    }

    public function testClient()
    {
        $pool = new ClientPool($this->factory);

        $this->factory
            ->expects($this->once())
            ->method('createClient')
            ->with(['info'])
            ->willReturn('client1');

        $pool->setConnections(['ulrich' => ['info']]);

        $this->assertEquals('client1', $pool->getClientByName('ulrich'));
    }

    public function testOverride()
    {
        $pool = new ClientPool($this->factory);

        $this->factory
            ->expects($this->once())
            ->method('createClient')
            ->with([
                'host' => 'newHost',
                'port' => 33,
                'user' => 'newUser',
                'password' => 'newPassword',
            ])
            ->willReturn('client1');

        $this->assertEquals($pool, $pool->setConnections(['ulrich' => [
            'host' => '127.0.0.1',
            'port' => 15672,
            'user' => 'guest',
            'password' => 'guest',
        ]]));

        $this->assertEquals($pool, $pool->overrideHost('newHost'));
        $this->assertEquals($pool, $pool->overridePort(33));
        $this->assertEquals($pool, $pool->overrideUser('newUser'));
        $this->assertEquals($pool, $pool->overridePassword('newPassword'));
        $pool->getClientByName('ulrich');
    }

    public function testGetUserByConnectionNameFailed()
    {
        $pool = new ClientPool($this->factory);

        $this->setExpectedException('\OutOfRangeException');
        $pool->getUserByConnectionName('ulrich');
    }

    public function testGetUserByConnectionName()
    {
        $pool = new ClientPool($this->factory);

        $pool->setConnections(['ulrich' => ['user' => 'guest']]);

        $this->assertEquals('guest', $pool->getUserByConnectionName('ulrich'));
    }
}

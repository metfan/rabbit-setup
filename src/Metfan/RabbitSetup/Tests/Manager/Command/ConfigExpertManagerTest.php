<?php
namespace Metfan\RabbitSetup\Tests\Manager\Command;

use Metfan\RabbitSetup\Manager\Command\ConfigExpertManager;
use PHPUnit\Framework\TestCase;

/**
 * Unit test of Metfan\RabbitSetup\Manager\Command\ConfigExpertManager
 *
 * @author Ulrich
 * @package Metfan\RabbitSetup\Tests\Manager\Command
 */
class ConfigExpertManagerTest extends TestCase
{
    public function test()
    {
        $vhostManager = $this->getMockBuilder('Metfan\RabbitSetup\Manager\RabbitMq\VhostManager')
            ->disableOriginalConstructor()
            ->getMock();

        $clientPool = $this->getMockBuilder('Metfan\RabbitSetup\Http\ClientPool')
            ->disableOriginalConstructor()
            ->getMock();

        $manager = new ConfigExpertManager($clientPool, $vhostManager);

        $clientPool
            ->expects($this->once())
            ->method('setConnections')
            ->with(['default' => []]);

        $vhostManager
            ->expects($this->once())
            ->method('processVhost')
            ->with('/', ['connection' => 'default', 'parameters' => []]);

        $manager->manageConfig([
            'connections' => ['default' => []],
            'vhosts' => ['/' => ['connection' => 'default', 'parameters' => []]]
        ]);
    }
}

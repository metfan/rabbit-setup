<?php
namespace Metfan\RabbitSetup\Tests\Command;

use Metfan\RabbitSetup\Command\ValidateExpertCommand;
use Pimple\Container;
use Symfony\Component\Console\Tester\CommandTester;


/**
 *
 *
 * @author Ulrich
 * @package Metfan\RabbitSetup\Tests\Command
 */
class ValidateExpertCommandTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        if (!defined('ROOT_PATH'))
            define('ROOT_PATH', realpath(__DIR__.'/../'));

        $container = new Container();
        $command = new ValidateExpertCommand($container);

        $tester = new CommandTester($command);
        $tester->execute(['configFile' => 'Parser/fixture/config.yml']);

        $this->assertEquals("<>[info] File don't show errors.\n", $tester->getDisplay(true));

        $this->assertInstanceOf('Symfony\Component\Console\Logger\ConsoleLogger', $container['logger']);
    }
}

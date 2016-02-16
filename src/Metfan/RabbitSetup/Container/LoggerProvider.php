<?php
namespace Metfan\RabbitSetup\Container;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * Override Logger defintion in container with ConsoleLogger
 *
 * @author Ulrich
 * @package Metfan\RabbitSetup\Container
 */
class LoggerProvider implements ServiceProviderInterface
{
    /**
     * @var OutputInterface
     */
    private $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function register(Container $container)
    {
        $container['command_output'] = $this->output;
        $container['logger'] = function($c){
            return new ConsoleLogger(
                $c['command_output'],
                [LogLevel::INFO => OutputInterface::VERBOSITY_NORMAL],
                [LogLevel::INFO => null]);
        };
    }
}

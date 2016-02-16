<?php
namespace Metfan\RabbitSetup\Command;

use Metfan\RabbitSetup\Container\LoggerProvider;
use Metfan\RabbitSetup\Parser\ParseExpertConfig;
use Pimple\Container;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * this command help tocheck config file is ok before submit it.
 *
 * @author Ulrich
 * @package Metfan\RabbitSetup\Command
 */
class ValidateExpertCommand extends Command
{
    /**
     * @var Container
     */
    private $container;

    public function __construct(Container $container)
    {
        parent::__construct();
        $this->container = $container;
    }

    protected function configure()
    {
        $this
            ->setName('rsetup:validate:expert')
            ->setDescription('Validate configuration file for expert mode')
            ->addArgument('configFile', InputArgument::REQUIRED, 'Configuration file');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /**
        * set context inside container
        */
        $this->container->register(new LoggerProvider($output));

        $parser = new ParseExpertConfig();
        $parser->parse($input->getArgument('configFile'));

        $this->container['logger']->info('<info>File don\'t show errors.</info>');
    }


}

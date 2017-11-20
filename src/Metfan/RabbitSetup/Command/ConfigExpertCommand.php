<?php
namespace Metfan\RabbitSetup\Command;

use Metfan\RabbitSetup\Container\LoggerProvider;
use Metfan\RabbitSetup\Manager\Command\ConfigExpertManager;
use Metfan\RabbitSetup\Parser\ParseExpertConfig;
use Pimple\Container;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * Command to apply config file to RabbitMQ instance
 *
 * @author Ulrich 
 * @package Metfan\RabbitSetup\Command
 */
class ConfigExpertCommand extends Command
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
            ->setName('rsetup:config:expert')
            ->setDescription('Apply configuration file to RabbitMQ')
            ->addArgument('configFile', InputArgument::REQUIRED, 'Configuration file')
            ->addOption('host', null, InputOption::VALUE_REQUIRED, 'Override host config', null)
            ->addOption('port', null, InputOption::VALUE_REQUIRED, 'Override port config', null)
            ->addOption('user', 'u', InputOption::VALUE_REQUIRED, 'Override user config', null)
            ->addOption('password', 'p', InputOption::VALUE_REQUIRED, 'Override password config', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->container->register(new LoggerProvider($output));
        $this->container['http_client_pool']
            ->overrideHost($input->getOption('host'))
            ->overridePort($input->getOption('port'))
            ->overrideUser($input->getOption('user'))
            ->overridePassword($input->getOption('password'));

        try {
            $logger = $this->container['logger'];
            $parser = new ParseExpertConfig();
            $config = $parser->parse($input->getArgument('configFile'));

            $manager = new ConfigExpertManager(
                $this->container['http_client_pool'],
                $this->container['manager_rabbitmq_vhost'],
                $logger);
            $manager->manageConfig($config);

        } catch (\Exception $e) {
            $logger->critical($e->getMessage());
            return 1;
        }

        return 0;
    }
}

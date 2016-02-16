<?php
namespace Metfan\RabbitSetup\Command;

use Metfan\RabbitSetup\Container\LoggerProvider;
use Metfan\RabbitSetup\Manager\Command\DeleteManager;
use Pimple\Container;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * This command allow to delete queues and exchanges in different way:
 * - all queues or exchanges: ./rabbit-setup rsetup:delete --exchanges "*" --queues "*"
 * - all queues or exhanges from one vhost: ./rabbit-setup rsetup:delete --exchanges "*" --queues "*" --vhost "myvhots"
 * - some queues or exchanges from one vhost ./rabbit-setup rsetup:delete --exchanges "firstExchange" --queues "queue1,queue2" --vhost "myvhots"
 *
 * @author Ulrich
 * @package Metfan\RabbitSetup\Command
 */
class DeleteCommand extends Command
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
            ->setName('rsetup:delete')
            ->setDescription('Delete exchanges and queues from vhosts, deletion can\'t be undo')
            ->addArgument('username', InputArgument::OPTIONAL, 'Username to connect RabbitMQ', 'guest')
            ->addArgument('password', InputArgument::OPTIONAL, 'Password to connect RabbitMQ', 'guest')
            ->addArgument('host', InputArgument::OPTIONAL, 'Host to connect RabbitMQ', '127.0.0.1')
            ->addArgument('port', InputArgument::OPTIONAL, 'Port to connect RabbitMQ', 15672)
            ->addOption('vhost', 'VH', InputOption::VALUE_REQUIRED, 'Which vhost to use (not mandatory if "*" == queues == exchanges)?', null)
            ->addOption('queues', 'Q', InputOption::VALUE_REQUIRED, 'Which queues will be deleted (coma separated, "*" for all)?', null)
            ->addOption('exchanges', 'E', InputOption::VALUE_REQUIRED, 'Which exchanges will be deleted(coma separated, "*" for all)?', null)
            ->addOption('policies', 'P', InputOption::VALUE_REQUIRED, 'Which policies will be deleted(coma separated, "*" for all)?', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /**
         * set context inside container
         */
        $this->container->register(new LoggerProvider($output));

        try {
            /** @var LoggerInterface $logger */
            $logger = $this->container['logger'];

            $connection = [
                'host' => $input->getArgument('host'),
                'port' => $input->getArgument('port'),
                'user' => $input->getArgument('username'),
                'password' => $input->getArgument('password')];

            $client = $this->container['curl_client_factory']->createClient($connection);
            $queueManager = $this->container['manager_rabbitmq_queue'];
            $exchangeManager = $this->container['manager_rabbitmq_exchange'];
            $policygeManager = $this->container['manager_rabbitmq_policy'];

            $manager = new DeleteManager($exchangeManager, $queueManager, $policygeManager, $logger);

            if ($input->hasOption('queues') && null !== $input->getOption('queues')) {
                $queueManager->setClient($client);
                $manager->deleteQueues($input->getOption('vhost'), $input->getOption('queues'));
            }

            if ($input->hasOption('exchanges') && null !== $input->getOption('exchanges')) {
                $exchangeManager->setClient($client);
                $manager->deleteExchanges($input->getOption('vhost'), $input->getOption('exchanges'));
            }

            if ($input->hasOption('policies') && null !== $input->getOption('policies')) {
                $policygeManager->setClient($client);
                $manager->deletePolicies($input->getOption('vhost'), $input->getOption('policies'));
            }
        } catch (\Exception $e) {
            $logger->critical($e->getMessage());
            return 1;
        }

        return 0;
    }
}
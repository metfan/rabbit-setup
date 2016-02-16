<?php
namespace Metfan\RabbitSetup\Manager\Command;

use Metfan\RabbitSetup\Http\ClientPool;
use Metfan\RabbitSetup\Manager\RabbitMq\VhostManager;

/**
 * Manager of rsetup:config:expert Command
 *
 * @author Ulrich
 * @package Metfan\RabbitSetup\Manager\Command
 */
class ConfigExpertManager
{
    /**
     * @var ClientPool
     */
    private $clientPool;

    /**
     * @var VhostManager
     */
    private $vhostManager;

    public function __construct(ClientPool $clientPool, VhostManager $vhostManager)
    {
        $this->clientPool = $clientPool;
        $this->vhostManager = $vhostManager;
    }

    /**
     * Check config with option resolver.
     * Apply each vhost config with VhostManager
     *
     * @param array $config
     */
    public function manageConfig(array $config)
    {
        $this->clientPool->setConnections($config['connections']);

        foreach ($config['vhosts'] as $name => $vhostConfig) {
            $this->vhostManager->processVhost($name, $vhostConfig);
        }
    }
}

<?php
namespace Metfan\RabbitSetup\Manager\RabbitMq;

use Metfan\RabbitSetup\Http\ClientInterface;

/**
 * Exchange manager, create, retrieve, delete
 *
 * @author Ulrich
 * @package Metfan\RabbitSetup\Manager
 */
class ExchangeManager extends BaseManager
{
    /**
     * Create an exchange
     *
     * @param string $exchangeName
     * @param array $options
     * @return void
     */
    public function create($exchangeName, array $options)
    {
        if (empty($options['arguments'])) {
            unset($options['arguments']);
        }

        $this->client->query(
            ClientInterface::METHOD_PUT,
            sprintf('/api/exchanges/%s/%s', $this->vhost, $exchangeName),
            $options);
        $this->logger->info(sprintf('Create exchange: <info>%s</info>', $exchangeName));
    }

    /**
     * Get all exchanges from a vhost or all exchange in your rabbitmq
     *
     * @param null $vhost
     * @return mixed
     */
    public function getAll($vhost = null)
    {
        return $this->findAllElements('/api/exchanges', $vhost);
    }

    /**
     * Delete an exchange from a vhost
     *
     * @param $vhost
     * @param $name
     * @return mixed
     */
    public function delete($vhost, $name)
    {
        return $this->deleteElement('/api/exchanges', $vhost, $name, 'exchange');
    }
}

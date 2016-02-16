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
        $url = '/api/exchanges';
        if ($vhost) {
            $url = sprintf('%s/%s', $url, $vhost);
        }

        return json_decode($this->client->query(ClientInterface::METHOD_GET, $url), true);
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
        $response = $this->client->query(ClientInterface::METHOD_DELETE, sprintf('/api/exchanges/%s/%s', $vhost, $name));

        $this->logger->info(
            sprintf('Delete exchange <info>%s</info> from vhost <info>%s</info>', $name, urldecode($vhost)));

        return $response;
    }
}

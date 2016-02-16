<?php
namespace Metfan\RabbitSetup\Manager\RabbitMq;

use Metfan\RabbitSetup\Http\ClientInterface;

/**
 * Policy manager, create, retrieve, delete
 *
 * @author Ulrich
 * @package Metfan\RabbitSetup\Manager\RabbitMq
 */
class PolicyManager extends BaseManager
{

    /**
     * Create element in RabbitMQ
     *
     * @param $name
     * @param array $options
     * @return void
     */
    public function create($name, array $options)
    {
        $this->client->query(
            ClientInterface::METHOD_PUT,
            sprintf('/api/policies/%s/%s', $this->vhost, $name),
            $options);
        $this->logger->info(sprintf('Create poilicy: <info>%s</info>', $name));
    }

    /**
     * Get all element of that type in vhost or in RabbitMQ if vhost = null
     *
     * @param null $vhost
     * @return array
     */
    public function getAll($vhost = null)
    {
        $url = '/api/policies';
        if ($vhost) {
            $url = sprintf('%s/%s', $url, $vhost);
        }

        return json_decode($this->client->query(ClientInterface::METHOD_GET, $url), true);
    }

    /**
     * delete an element on RabbitMQ
     *
     * @param $vhost
     * @param $name
     * @return mixed
     */
    public function delete($vhost, $name)
    {
        $response = $this->client->query(ClientInterface::METHOD_DELETE, sprintf('/api/policies/%s/%s', $vhost, $name));

        $this->logger->info(
            sprintf('Delete policy <info>%s</info> from vhost <info>%s</info>', $name, urldecode($vhost)));

        return $response;
    }
}
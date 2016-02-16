<?php
namespace Metfan\RabbitSetup\Manager\RabbitMq;

use Metfan\RabbitSetup\Http\ClientInterface;

/**
 * Queue manager, create, retrieve, delete queue and bind queues to exchange
 *
 * @author Ulrich
 * @package Metfan\RabbitSetup\Manager
 */
class QueueManager extends BaseManager
{
    /**
     * Create a queue
     *
     * @param $queueName
     * @param array $options
     * @return void
     */
    public function create($queueName, array $options)
    {
        if (empty($options['arguments'])) {
            unset($options['arguments']);
        }

        $bindings = $options['bindings'];
        unset($options['bindings']);

        $this->client->query(
            ClientInterface::METHOD_PUT,
            sprintf('/api/queues/%s/%s', $this->vhost, $queueName),
            $options);
        $this->logger->info(sprintf('Create queue: <info>%s</info>', $queueName));

        foreach($bindings as $binding) {
            $this->client->query(
                ClientInterface::METHOD_POST,
                sprintf('/api/bindings/%s/e/%s/q/%s', $this->vhost, $binding['exchange'], $queueName),
                ['routing_key' => $binding['routing_key']]);
            $this->logger->info(sprintf(
                'Bind exchange <info>%s</info> with queue <info>%s</info> using routing key <info>%s</info>',
                $binding['exchange'],
                $queueName,
                $binding['routing_key']));
        }

    }

    /**
     * Get all queue from a vhost or all queue in your rabbitmq
     *
     * @param string|null $vhost
     * @return mixed
     */
    public function getAll($vhost = null)
    {
        $url = '/api/queues';
        if ($vhost) {
            $url = sprintf('%s/%s', $url, $vhost);
        }

        return json_decode($this->client->query(ClientInterface::METHOD_GET, $url), true);
    }

    /**
     * Delete a queue from a vhost
     *
     * @param $vhost
     * @param $name
     * @return mixed
     */
    public function delete($vhost, $name)
    {
        $response = $this->client->query(ClientInterface::METHOD_DELETE, sprintf('/api/queues/%s/%s', $vhost, $name));

        $this->logger->info(
            sprintf('Delete queue <info>%s</info> from vhost <info>%s</info>', $name, urldecode($vhost)));

        return $response;
    }
}

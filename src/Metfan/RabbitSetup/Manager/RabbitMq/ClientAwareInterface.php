<?php
namespace Metfan\RabbitSetup\Manager\RabbitMq;

use Metfan\RabbitSetup\Http\ClientInterface;

/**
 * Interface about manager need tyo know whish vhost to work with
 *
 * @author Ulrich
 * @package Metfan\RabbitSetup\Manager\RabbitMq
 */
interface ClientAwareInterface
{
    /**
     * Set client to access RabbitMQ API
     *
     * @param ClientInterface $client
     * @return $this
     */
    public function setClient(ClientInterface $client);
}
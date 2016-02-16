<?php
namespace Metfan\RabbitSetup\Manager\RabbitMq;

/**
 * Simple trait implentation of VhostAwareInterface
 *
 * @author Ulrich
 * @package Metfan\RabbitSetup\Manager\RabbitMq
 */
trait VhostAwareTrait
{
    protected $vhost;

    /**
     * @param mixed $vhost
     * @return $this
     */
    public function setVhost($vhost)
    {
        $this->vhost = $vhost;

        return $this;
    }
}
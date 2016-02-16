<?php
namespace Metfan\RabbitSetup\Manager\RabbitMq;

/**
 * Interface about manager need tyo know whish vhost to work with
 *
 * @author Ulrich
 * @package Metfan\RabbitSetup\Manager\RabbitMq
 */
interface VhostAwareInterface
{
    /** Set vhost
     *
     * @param $vhost
     * @return mixed
     */
    public function setVhost($vhost);
}

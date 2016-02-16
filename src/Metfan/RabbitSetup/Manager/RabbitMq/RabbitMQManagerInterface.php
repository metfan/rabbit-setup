<?php
namespace Metfan\RabbitSetup\Manager\RabbitMq;


/**
 * Interface about basic rabittmq manager element like queue and exchange
 *
 * @author Ulrich
 * @package Metfan\RabbitSetup\Manager\RabbitMq
 */
interface RabbitMQManagerInterface extends VhostAwareInterface, ClientAwareInterface
{
    /**
     * Create element in RabbitMQ
     *
     * @param $name
     * @param array $options
     * @return mixed
     */
    public function create($name, array $options);

    /**
     * Get all element of that type in vhost or in RabbitMQ if vhost = null
     *
     * @param null $vhost
     * @return array
     */
    public function getAll($vhost = null);

    /**
     * delete an element on RabbitMQ
     *
     * @param $vhost
     * @param $name
     * @return mixed
     */
    public function delete($vhost, $name);
}

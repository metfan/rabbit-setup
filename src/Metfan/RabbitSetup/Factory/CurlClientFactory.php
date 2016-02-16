<?php
namespace Metfan\RabbitSetup\Factory;

use Metfan\RabbitSetup\Http\CurlClient;


/**
 * Factory for http curl client class
 *
 * @author Ulrich
 * @package Metfan\Factory
 */
class CurlClientFactory
{
    /**
     * controle $options and return newly created CurlClient instance
     *
     * @param $options
     * @return CurlClient
     */
    public function createClient($options)
    {
        return new CurlClient($options['host'], $options['port'], $options['user'], $options['password']);
    }
}

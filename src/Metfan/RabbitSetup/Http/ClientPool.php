<?php
namespace Metfan\RabbitSetup\Http;

use Metfan\RabbitSetup\Factory\CurlClientFactory;

/**
 * Pool of HttpClient by connection.
 * Lazy createclient on demand
 *
 * @author Ulrich
 * @package Metfan\RabbitSetup\Http
 */
class ClientPool
{
    /**
     * @var CurlClientFactory
     */
    private $factory;

    /**
     * @var array
     */
    private $pool = array();

    /**
     * @var array
     */
    private $connections;

    private $user;

    private $password;

    public function __construct(CurlClientFactory $factory)
    {
        $this->factory = $factory;
        $this->connections = [];
    }

    /**
     * @param mixed $connections
     * @return $this
     *
     */
    public function setConnections($connections)
    {
        $this->connections = $connections;

        return $this;
    }

    /**
     * @param mixed $user
     * @return $this
     */
    public function overrideUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @param mixed $password
     * @return $this
     */
    public function overridePassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Return a Client by his name
     *
     * @param $name
     * @return ClientInterface
     */
    public function getClientByName($name)
    {
        if (!array_key_exists($name, $this->pool)) {
            $this->createClient($name);
        }

        return $this->pool[$name];
    }

    /**
     * create a client
     *
     * @param $name
     */
    private function createClient($name)
    {
        if (!array_key_exists($name, $this->connections)) {
            throw new \OutOfRangeException(sprintf('Expected connection %s doesn\'t exists', $name));
        }

        if (null !== $this->user) {
            $this->connections[$name]['user'] = $this->user;
        }

        if (null !== $this->password) {
            $this->connections[$name]['password'] = $this->password;
        }

        $this->pool[$name] = $this->factory->createClient($this->connections[$name]);
    }
}

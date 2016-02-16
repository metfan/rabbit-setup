<?php
namespace Metfan\RabbitSetup\Http;

/**
 * Interface about HttpClient
 *
 * @author Ulrich
 * @package Metfan\RabbitSetup\Http
 */
interface ClientInterface
{
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_DELETE = 'DELETE';
    const METHOD_PATCH = 'PATCH';
    const METHOD_LINK = 'LINK';
    const METHOD_UNLINK = 'UNLINK';
    const METHOD_HEAD = 'HEAD';
    const METHOD_OPTIONS = 'OPTIONS';

    /**
     * @param $method
     * @param $uri
     * @param array $parameters
     * @return mixed
     */
    public function query($method, $uri, array $parameters = array());
}

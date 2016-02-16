<?php
namespace Metfan\RabbitSetup\Http;



/**
 * Curl client to use RabbitMQ API
 *
 * @author Ulrich
 * @package Metfan\RabbitSetup\Http
 */
class CurlClient implements ClientInterface
{
    /**
     * @var string
     */
    private $host;

    /**
     * @var int
     */
    private $port;

    /**
     * @var string
     */
    private $user;

    /**
     * @var string
     */
    private $password;

    public function __construct($host, $port, $user, $password)
    {
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->password = $password;
    }

    /**
     * execute http request
     *
     * @param $method
     * @param $uri
     * @param array $parameters
     * @return mixed
     */
    public function query($method, $uri, array $parameters = array())
    {
        $options = array_merge(
            $this->buildUri($uri),
            $this->basicOptions(),
            $this->setMethodOption($method)
        );

        if (ClientInterface::METHOD_GET !==$method
            && ClientInterface::METHOD_DELETE !==$method
            && !empty($parameters)) {
            $options['CURLOPT_POSTFIELDS'] = json_encode($parameters);
        }

        $curl = $this->buildCurl($options);
        $rawResponse = curl_exec($curl);
        $curlInfo = curl_getinfo($curl);
        $curlError = curl_error($curl);
        $curlErno = curl_errno($curl);
        curl_close($curl);

        if (false === $rawResponse) {
            throw new \RuntimeException(sprintf('Curl Error: %s - ', $curlError, $curlErno));
        }



        if (!in_array($curlInfo['http_code'], array(200, 201, 204))) {
            throw new \RuntimeException(sprintf(
                'Receive code %d instead of 200, 201 or 204. Url: %s. Body: %s',
                $curlInfo['http_code'],
                $options['CURLOPT_URL'],
                $rawResponse
            ));
        }

        return $rawResponse;
    }

    /**
     * return basic options configuration for curl
     *
     * @return array
     */
    private function basicOptions()
    {
        return [
            'CURLOPT_HTTPHEADER' => array('Content-Type: application/json'),
            'CURLOPT_PORT' => $this->port,
            'CURLOPT_VERBOSE' => false,
            'CURLOPT_HEADER' => false,
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_USERPWD' => sprintf('%s:%s', $this->user, $this->password)
        ];
    }

    /**
     * build uri
     *
     * @param $uri
     * @return array
     */
    private function buildUri($uri)
    {
        if ('/' !== substr($this->host, -1) && '/' !== substr($uri, 0, 1)) {
            $finalUri = $this->host.'/'.$uri;
        } else {
            $finalUri = $this->host.$uri;
        }

        return ['CURLOPT_URL' => $finalUri];
    }

    /**
     * return correct curl option
     * @param $method
     * @return array
     */
    private function setMethodOption($method)
    {
        if ($method == ClientInterface::METHOD_GET) {
            return ['CURLOPT_HTTPGET' => null];
        } elseif ($method == ClientInterface::METHOD_POST) {
            return ['CURLOPT_POST' => null];
        } elseif ($method == ClientInterface::METHOD_PUT) {
            return ['CURLOPT_CUSTOMREQUEST' => 'PUT'];
        } elseif ($method == ClientInterface::METHOD_DELETE) {
            return ['CURLOPT_CUSTOMREQUEST' => 'DELETE'];
        } elseif ($method == ClientInterface::METHOD_PATCH) {
            return ['CURLOPT_CUSTOMREQUEST' => 'PATCH'];
        } elseif ($method == ClientInterface::METHOD_LINK) {
            return ['CURLOPT_CUSTOMREQUEST' => 'LINK'];
        } elseif ($method == ClientInterface::METHOD_UNLINK) {
            return ['CURLOPT_CUSTOMREQUEST' => 'UNLINK'];
        } else {
            throw new \OutOfRangeException('Method '.$method.' currently not supported');
        }
    }

    /**
     * init curl with  all options
     *
     * @param array $options
     * @return resource
     */
    private function buildCurl(array $options)
    {
        $curl = curl_init();
        foreach ($options as $curlOption => $value) {
            curl_setopt($curl, constant($curlOption), $value);
        }

        return $curl;
    }
}
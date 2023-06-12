<?php

namespace GoPay\Http;

use GoPay\Http\Log\Logger;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;

class JsonBrowser
{

    private $logger;
    private $timeout;

    public function __construct(Logger $l, $timeoutInSeconds)
    {
        $this->logger = $l;
        $this->timeout = $timeoutInSeconds;
    }

    public function send(Request $r)
    {
        try {
            if (class_exists('\GuzzleHttp\Message\Request')) {
                $client = new GuzzleClient();
                $guzzRequest = $client->createRequest($r->method, $r->url);
                $guzzRequest->setHeaders($r->headers);
                $guzzRequest->setBody(\GuzzleHttp\Stream\Stream::factory($r->body));
            } else {
                $client = new GuzzleClient(['timeout' => $this->timeout]);
                $guzzRequest = new \GuzzleHttp\Psr7\Request($r->method, $r->url, $r->headers, $r->body);
            }
            $guzzResponse = $client->send($guzzRequest);
            $response = new Response((string)$guzzResponse->getBody());
            $response->statusCode = (string)$guzzResponse->getStatusCode();
            $response->json = json_decode((string)$response, true);
            $this->logger->logHttpCommunication($r, $response);
            return $response;
        } catch (ClientException $e) {
            if ($e->hasResponse()) {
                $response = new Response($e->getResponse()->getBody());
                $response->json = json_decode($e->getResponse()->getBody(), true);
                $response->statusCode = $e->getCode();
                $this->logger->logHttpCommunication($r, $response);
                return $response;
            }
        } catch (\Exception $ex) {
            $response = new Response($ex->getMessage());
            $response->statusCode = 500;
            $response->json = json_decode("{}", true);
            $this->logger->logHttpCommunication($r, $response);
            return $response;
        }
    }

    /**
     * @return Logger
     */
    public function getLogger(): Logger
    {
        return $this->logger;
    }
}

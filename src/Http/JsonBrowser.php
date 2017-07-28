<?php

namespace GoPay\Http;

use GoPay\Http\Log\Logger;
use GuzzleHttp\Client as GuzzleClient;

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
            if(class_exists('\GuzzleHttp\Message\Message')) {
                $client = new GuzzleClient();
                $guzzRequest = $client->createRequest($r->method, $r->url);
                $guzzRequest->setHeaders($r->headers);
                $guzzRequest->setBody(\GuzzleHttp\Stream\Stream::factory($r->body));
            }else {
                $client = new GuzzleClient(['timeout' => $this->timeout]);
                $guzzRequest = new \GuzzleHttp\Psr7\Request($r->method, $r->url, $r->headers, $r->body);
            }
            $guzzResponse = $client->send($guzzRequest);
            $response = new Response((string) $guzzResponse->getBody());
            $response->statusCode = (string) $guzzResponse->getStatusCode();
            $response->json = json_decode((string) $response, true);
        } catch (\Exception $e) {
            $response = new Response($e->getMessage());
            $response->statusCode = 500;
        }
        $this->logger->logHttpCommunication($r, $response);
        return $response;
    }
}

<?php

namespace GoPay\Http;

use GoPay\Http\Log\Logger;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;

class JsonBrowser
{

    private Logger $logger;
    /** @var int|float passed to Guzzle as is */
    private $timeout;

    /**
     * @param int|float $timeoutInSeconds
     */
    public function __construct(Logger $l, $timeoutInSeconds)
    {
        $this->logger = $l;
        $this->timeout = $timeoutInSeconds;
    }

    /**
     * Never throws, errors are converted to Response (status 500 for non-HTTP errors).
     *
     * @return Response
     */
    public function send(Request $r)
    {
        // ClientException always has a response in Guzzle 7, so the try block always returns
        // @phpstan-ignore return.missing
        try {
            // Legacy Guzzle 5 API, unreachable with the required Guzzle 7
            if (class_exists('\GuzzleHttp\Message\Request')) {
                $client = new GuzzleClient();
                $guzzRequest = $client->createRequest($r->method, $r->url); // @phpstan-ignore method.notFound
                $guzzRequest->setHeaders($r->headers);
                $guzzRequest->setBody(\GuzzleHttp\Stream\Stream::factory($r->body)); // @phpstan-ignore class.notFound
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

<?php

namespace Fliq\Ipfs;

use GuzzleHttp\Client;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Stream;
use function str_contains;

class Gateway
{
    protected Client $client;

    public function __construct(
        protected string $gatewayHost,
        protected string $protocol = 'https',
        protected string $urlMode = 'subdomain',
    ) {
        $this->client = new Client();
    }

    public function getUrl($uri) : string
    {
        return match ($this->urlMode) {
            'subdomain' => $this->getSubdomainUrl($uri),
            'path'      => $this->getPathUrl($uri),
        };
    }

    public function read(string $uri) : PromiseInterface
    {
        return $this
            ->client
            ->getAsync($this->getUrl($uri))
            ->then(function ($response) {
                return $response->getBody();
            });
    }

    public function cat(string $uri) : PromiseInterface
    {
        return $this->read($uri);
    }

    public function get(string $uri) : PromiseInterface
    {
        return $this->read($uri)->then(function (Stream $response) {
            return $response->getContents();
        });
    }

    public function json(string $uri) : PromiseInterface
    {
        return $this->get($uri)->then(function ($content) {
            return json_decode($content, JSON_OBJECT_AS_ARRAY);
        });
    }

    public function splitCidAndPath(string $uri) : array
    {
        $pos = strpos($uri, '/');

        $cid  = substr($uri, 0, $pos);
        $path = substr($uri, $pos);

        return [$cid, $path];
    }

    protected function getSubdomainUrl($uri) : string
    {
        if (str_contains($uri, '/')) {
            [$cid, $path] = $this->splitCidAndPath($uri);

            return $this->protocol . '://' . $cid . '.ipfs.' . $this->gatewayHost . $path;
        }

        return $this->protocol . '://' . $uri . '.ipfs.' . $this->gatewayHost;
    }

    protected function getPathUrl($uri)
    {
        return $this->protocol . '://' . $this->gatewayHost . '/ipfs/' . $uri;
    }
}
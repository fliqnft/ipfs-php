<?php

namespace Fliq\Ipfs;

use Fliq\Ipfs\Commands\Add;
use Fliq\Ipfs\Commands\Cat;
use Fliq\Ipfs\Commands\Ls;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Promise;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Stream;
use GuzzleHttp\Psr7\Utils;
use Illuminate\Support\Facades\Http;

class Ipfs
{
    protected Client $api;
    protected Client $gateway;

    public function __construct(
        string $host = 'localhost',
        string $gatewayPort = '8080',
        string $apiPort = '5001',
        string $protocol = 'http',
    ) {
        $this->api = new Client([
            'base_uri' => "{$protocol}://{$host}:{$apiPort}/api/v0/"
        ]);

        $this->gateway = new Client([
            'base_uri' => "{$protocol}://{$host}:{$gatewayPort}/api/v0/"
        ]);
    }

    public function version() : Promise\PromiseInterface
    {
        return $this->gateway->getAsync('version')->then(function ($response) {
            return json_decode($response->getBody()->getContents(), JSON_OBJECT_AS_ARRAY);
        });
    }

    /**
     * https://docs.ipfs.tech/reference/kubo/rpc/#api-v0-add
     */
    public function add($resource, array $options = []) : Promise\PromiseInterface
    {
        $command = new Add($this->api, $options);

        return $command->handle($resource);
    }

    /**
     * Gets the contents from the file and returns a Psr7\Stream
     * @param  mixed  $args
     * @return Promise\PromiseInterface
     */
    public function cat(mixed $args) : Promise\PromiseInterface
    {
        $command = new Cat($this->gateway);

        return $command->handle($args);
    }

    /**
     * Gets the contents of the file as a string
     * @param  mixed  $args
     * @return Promise\PromiseInterface
     */
    public function get(mixed $args) : Promise\PromiseInterface
    {
        return $this->cat($args)->then(function (Stream $response) {
            return $response->getContents();
        });
    }

    /**
     * Gets the contents of the file and json decodes it
     * @param  mixed  $args
     * @return Promise\PromiseInterface
     */
    public function json(mixed $args) : Promise\PromiseInterface
    {
        return $this->get($args)->then(function (string $contents) {
            return json_decode($contents, JSON_OBJECT_AS_ARRAY);
        });
    }

    public function ls(mixed $args) : Promise\PromiseInterface
    {
        $command = new Ls($this->gateway);

        return $command->handle($args);
    }


    public function apiCall(string $uri, array $options = []) : Promise\PromiseInterface
    {
        return $this->api->postAsync($uri, $options)->then(function ($response) {
            return $response->getBody();
        });
    }

    public function gatewayCall(string $uri, array $options = []) : Promise\PromiseInterface
    {
        return $this->gateway->getAsync($uri, $options)->then(function ($response) {
            return $response->getBody();
        });
    }
}
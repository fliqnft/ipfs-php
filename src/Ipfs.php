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

    public function cat(mixed $args) : Promise\PromiseInterface
    {
        $command = new Cat($this->gateway);

        return $command->handle($args);
    }

    public function ls(mixed $args) : Promise\PromiseInterface
    {
        $command = new Ls($this->gateway);

        return $command->handle($args);
    }

}
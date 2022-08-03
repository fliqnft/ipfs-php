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
    protected Client $client;
    protected string $method;

    public function __construct(
        string $host = 'localhost',
        string $port = null,
        string $protocol = 'http',
        public string $mode = 'gateway',
    ) {
        $this->method = match ($this->mode) {
            'api'     => 'POST',
            'gateway' => 'GET',
            default   => throw new IpfsApiException('mode must be api or gateway'),
        };

        $port = $port ?? match ($this->mode) {
                'api'     => '5001',
                'gateway' => '8080',
            };

        $this->client = new Client([
            'base_uri' => "{$protocol}://{$host}:{$port}/api/v0/"
        ]);
    }

    public function version() : Promise\PromiseInterface
    {
        return $this->client->requestAsync($this->method, 'version')->then(function ($response) {
            return json_decode($response->getBody()->getContents(), JSON_OBJECT_AS_ARRAY);
        });
    }

    /**
     * Adds files to IPFS.
     *
     * Can upload multiple files use array keys as file names.
     * arrays will be json encoded and uploaded as a string.
     * example:
     *
     * $ipfs->add(['file.json' => ['key' => 'value']]);
     *
     * reference: https://docs.ipfs.tech/reference/kubo/rpc/#api-v0-add
     *
     * @param string|array|resource $resource
     * @param array $options
     *
     * @throws IpfsApiException
     */
    public function add($resource, array $options = []) : Promise\PromiseInterface
    {
        if ($this->mode != 'api') {
            throw new IpfsApiException('Must be in API mode to add files');
        }

        $command = new Add($this, $options);

        return $command->handle($resource);
    }

    /**
     * Gets the contents from the file and returns a Psr7\Stream
     * @param  mixed  $args
     * @return Promise\PromiseInterface
     */
    public function cat(mixed $args) : Promise\PromiseInterface
    {
        $command = new Cat($this);

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

        $command = new Ls($this);
        return $command->handle($args);
    }

    /**
     * @param  string  $uri
     * @param  array  $options
     * @return Promise\PromiseInterface
     */
    public function call(string $uri, array $options = []) : Promise\PromiseInterface
    {
        return $this->client->requestAsync($this->method, $uri, $options);
    }
}
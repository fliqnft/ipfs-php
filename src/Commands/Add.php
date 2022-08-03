<?php

namespace Fliq\Ipfs\Commands;

use Fliq\Ipfs\Ipfs;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class Add
{

    public function __construct(protected Ipfs $client, array $options = [])
    {
        $this->options = array_merge([
            'cid-version' => '1',
        ], $options);
    }

    public function handle(...$resources) : PromiseInterface
    {
        $promise = $this->client->call('add', [
            'multipart' => $this->build($resources),
            'query'     => $this->options,
        ]);

        return $promise->then(function (Response $response) {
            $arr = preg_split('/\n/', $response->getBody()->getContents());

            $filtered = array_filter($arr, fn($item) => trim($item) !== "");

            return array_map(fn($item) => json_decode($item, JSON_OBJECT_AS_ARRAY), $filtered);
        });
    }

    protected function build($resources) : array
    {
        $headers = [];
        $parts   = [];

        if (is_array($resources)) {
            foreach ($resources as $key => $resource) {
                // recursively resolve part if we have a numeric key.
                if (is_numeric($key)) {
                    $parts = array_merge($parts, $this->build($resource));
                }
                else { // otherwise we have a string as a key such as 'file.json' => [],
                    $headers  = $this->makeHeaders($key);

                    $parts [] = $this->makePart($headers, $resource);
                }
            }
        }
        else {
            $parts [] = $this->makePart($headers, $resources);
        }

        return $parts;
    }

    protected function makePart(array $headers, $resource) : array
    {
        if (is_array($resource)) {
            $resource = json_encode($resource);
        }

        return [
            'name'     => 'path',
            'headers'  => $headers,
            'contents' => $resource,
        ];
    }

    protected function makeHeaders(string $key) : array
    {
        return [
            'Content-Disposition' => 'form-data; name="file"; filename="' . $key . '"',
            'Content-Type'        => 'application/octet-stream',
        ];
    }

}
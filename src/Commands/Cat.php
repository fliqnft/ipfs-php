<?php

namespace Fliq\Ipfs\Commands;

use GuzzleHttp\Client;

class Cat
{

    public function __construct(protected Client $client)
    {
    }

    public function handle(string|array $args)
    {
        if(is_string($args)) {
            $args = ['arg' => $args];
        }

        return $this->client->getAsync('cat', [
            'query' => $args,
        ])->then(function ($response) {
            return $response->getBody();
        });
    }

}
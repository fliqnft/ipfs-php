<?php

namespace Fliq\Ipfs\Commands;

use Fliq\Ipfs\Ipfs;
use GuzzleHttp\Client;

class Cat
{

    public function __construct(protected Ipfs $client)
    {
    }

    public function handle(string|array $args)
    {
        if(is_string($args)) {
            $args = ['arg' => $args];
        }

        return $this->client->call('cat', [
            'query' => $args,
        ])->then(function ($response) {
            return $response->getBody();
        });
    }

}
<?php

namespace Fliq\Ipfs\Commands;

use Fliq\Ipfs\Ipfs;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

class Ls
{

    public function __construct(protected Ipfs $client)
    {

    }

    public function handle(array|string $args)
    {
        if (is_string($args)) {
            $args = ['arg' => $args];
        }

        return $this->client->call('ls', [
            'query' => $args,
        ])->then(function (Response $response) {
            return json_decode(
                $response->getBody()->getContents(),
                JSON_OBJECT_AS_ARRAY,
            );
        });
    }

}
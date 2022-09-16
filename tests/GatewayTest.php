<?php
// GatewayTest

use Fliq\Ipfs\Gateway;
use GuzzleHttp\Psr7\Stream;

beforeEach(function () {
    $this->gateway = new Gateway('nftstorage.link');
});

it('can read from a gateway as a stream', function () {
    $stream = $this->gateway->read('bafybeiedv7sowwxamly4oicivudp45rsfvbklnf3fvbvonxrwoxqylhtwq/0.json')->wait();

    expect($stream)->toBeInstanceOf(Stream::class);
});

it('can get the contents of a file', function () {
    $contents = $this->gateway->get('bafybeiedv7sowwxamly4oicivudp45rsfvbklnf3fvbvonxrwoxqylhtwq/0.json')->wait();

    expect($contents)->toBeString();
});


it('can get the contents as json from a file', function () {
    $json = $this->gateway->json('bafybeiedv7sowwxamly4oicivudp45rsfvbklnf3fvbvonxrwoxqylhtwq/0.json')->wait();

    expect($json)
        ->toBeArray()
        ->toHaveKeys(['tokenId', 'attributes', 'image']);

});
<?php
// CatTest

use Fliq\Ipfs\Commands\Cat;
use Fliq\Ipfs\Ipfs;
use GuzzleHttp\Psr7\Stream;

it('reads a file', function () {
    $file = api()->add('Hello, IPFS')->wait()[0];

    $command = new Cat(gateway());

    /** @var Stream $stream */
    $stream = $command->handle($file['Hash'])->wait();

    expect($stream->getContents())->toEqual('Hello, IPFS');
});


it('reads a file in a folder', function () {
    $dir  = api()->add(
        ['hello.txt' => 'Hello, IPFS'],
        ['wrap-with-directory' => true]
    )->wait()[1]; // the last item is the dir

    $command = new Cat(gateway());

    /** @var Stream $stream */
    $stream = $command->handle("{$dir['Hash']}/hello.txt")->wait();

    expect($stream->getContents())->toEqual('Hello, IPFS');
});
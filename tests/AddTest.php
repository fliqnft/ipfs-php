<?php
// AddTest

use Fliq\Ipfs\Commands\Add;
use Fliq\Ipfs\Ipfs;
use GuzzleHttp\Psr7\Utils;

beforeEach(function () {
    $this->ipfs = api();
});

it('adds from a string', function () {
    $command = new Add(api());

    $response = $command->handle('Hello, IPFS!')->wait()[0];

    expect($response)->toHaveKey('Hash');

    $file = $this->ipfs->cat($response['Hash'])->wait();

    expect($file->getContents())->toEqual('Hello, IPFS!');
});

it('adds multiple files', function () {
    $command = new Add(api());

    $response = $command->handle('Hello, IPFS!', 'Another one!')->wait();

    expect($response)->toHaveCount(2);
});

it('wraps in a directory', function () {
    $command = new Add(api(), ['wrap-with-directory' => true]);

    $response = $command->handle('Hello, IPFS!', 'Another one!')->wait();

    expect($response)->toHaveCount(3);
});

it('can upload from a resource', function () {
    $command = new Add(api());

    $resource = Utils::tryFopen(__DIR__ . '/uploads/foo.txt', 'r');

    $response = $command->handle($resource)->wait();

    expect($response[0])->toHaveKey('Hash');
});

it('adds array as json', function () {
    $command = new Add(api());

    $response = $command->handle([
        'file.json' => [
            'foo' => 'bar',
        ],
    ])->wait();

    expect($response[0]['Name'])->toEqual('file.json');
});
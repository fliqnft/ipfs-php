<?php
// IpfsTest

use Fliq\Ipfs\Ipfs;

test('version', function () {

    $ipfs = new Ipfs();

    $response = $ipfs->version()->wait();

    expect($response)
        ->toHaveKeys([
            'Version',
            'Commit',
            'Repo',
            'System',
            'Golang',
        ]);
});

test('add', function () {
    $ipfs = new Ipfs();

    $response = $ipfs->add('Hello, IPFS')->wait();

    expect($response[0])->toHaveKey('Hash');
});

test('cat', function () {
    $ipfs = new Ipfs();

    $file = $ipfs->add('Hello, IPFS')->wait()[0];

    $text = $ipfs->cat($file['Hash'])->wait();

    expect($text->getContents())->toEqual('Hello, IPFS');
});


test('ls', function () {
    $ipfs = new Ipfs();

    $dir = $ipfs->add(['hello.txt' => 'Hello, IPFS'], ['wrap-with-directory' => true])->wait()[1];

    $list = $ipfs->ls($dir['Hash'])->wait();

    expect($list['Objects'][0]['Links'][0]['Name'])->toEqual('hello.txt');
});
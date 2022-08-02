<?php
// LsTest

use Fliq\Ipfs\Commands\Ls;
use Fliq\Ipfs\Ipfs;

beforeEach(function() {
    $this->ipfs = new Ipfs();
});

it('lists files', function () {
    $response = $this->ipfs->add(
        ['foo.txt' => 'foo'],
        ['wrap-with-directory' => true]
    )->wait()[1];

    $command = new Ls(gateway());

    $list = $command->handle($response['Hash'])->wait();

    expect($list['Objects'][0]['Links'][0]['Name'])->toEqual('foo.txt');
});
<?php

//uses(TestCase::class)->in('./');


use Fliq\Ipfs\Ipfs;
use GuzzleHttp\Client;

function api() : Ipfs
{
    return new Ipfs(mode: 'api');
}

function gateway() : Ipfs
{
    return new Ipfs();
}
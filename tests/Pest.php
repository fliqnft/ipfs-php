<?php

//uses(TestCase::class)->in('./');


use GuzzleHttp\Client;

function api() : Client
{
    return new Client([
        'base_uri' => "http://localhost:5001/api/v0/"
    ]);
}

function gateway() : Client
{
    return new Client([
        'base_uri' => "http://localhost:8080/api/v0/"
    ]);
}
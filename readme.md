# Ipfs

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]

Interact with IPFS using the [RPC api](https://docs.ipfs.tech/reference/kubo/rpc/).

Take a look at [contributing.md](contributing.md) to see a to do list.

## Installation

Via Composer

``` bash
composer require fliq/ipfs
```

## Usage

To get started with IPFS visit the [IPFS documentation](https://docs.ipfs.tech/how-to/command-line-quick-start)

This package uses [Guzzle](https://github.com/guzzle/guzzle) every request is made using the `requestAsync` method, and
every method on the Ipfs class
returns a guzzles [PromiseInterface](https://docs.guzzlephp.org/en/stable/quickstart.html#async-requests).

There are two modes for the ipfs client 'api' and 'gateway'. Gateway is accessible publicly for read only operations
and api can write.

```php
use Fliq\Ipfs\Ipfs;

$ipfs = new Ipfs('localhost', '8080', 'http', 'gateway');

$data = $ipfs->cat('QmSgvgwxZGaBLqkGyWemEDqikCqU52XxsYLKtdy3vGZ8uq')->wait(); // spaceship-launch.jpg

```

To write files you must be in 'api' mode.

```php
use Fliq\Ipfs\Ipfs;

$ipfs = new Ipfs('localhost', '5001', 'http', 'api');

$result = $ipfs->add('Hello, IPFS')->wait();

$cid = $result[0]['Hash']; // content identifier
```

You can add files flexibly to IPFS with support for strings, php resources and encode arrays into json, and multiple
files at once all wrapped into a directory.

```php
use GuzzleHttp\Psr7\Utils;

$ipfs = new Ipfs('localhost', '5001', 'http', 'api');

$file = Utils::tryFopen('path/to/file.jpeg')

$results = $ipfs->add([
    'file.jpeg' => $file,
    'meta.json' => [
        'name' => 'My file',
        'description' => 'file description',
        'properties' => [...],
    ],
], ['wrap-with-directory' => true])->wait();

$directoryCid = $results[2]['Hash'];
```

Retrieving data from IPFS. With the cat method you can get a Psr7 stream as a result, but for convenience you can
use the `get()` and `json()` methods to read files also.

```php
$data = $ipfs->cat('Qm...')
               ->then(fn($stream) => $stream->getContents())
               ->then(fn($str) => json_decode($str, 1))
               ->wait();

// is the same as.

$data = $ipfs->get('Qm...')
             ->then(fn($str) => json_decode($str, 1))
             ->wait();

// is the same as

$data = $ipfs->json('Qm...')->wait();

```

Because every request is async you can make multiple requests at the same time.

```php
use GuzzleHttp\Promise;

$promises = [];

$promises[] = $ipfs->add('Hello, IPFS');
$promises[] = $ipfs->cat('QmSgvgwxZGaBLqkGyWemEDqikCqU52XxsYLKtdy3vGZ8uq');

$responses = Promise\Utils::unwrap($promises);
```

Lastly you can call any endpoint using the `call()` method.

```php
$ipfs->call('name/resolve', ['query' => $args])->then(function(Response $response) {
    // handle response.
});
```

## Methods:

### version()
Gets Kubo node information.

### add()
Adds files to IPFS 

### cat()
Reads files returns a Stream 

#### get()
Reads files returns a string

#### json()
Reads files returns an array

### ls()
Reads content from CID

### call()
Will call 



## Change log

Please see the [changelog](changelog.md) for more information on what has changed recently.

## Testing

You'll need a local IPFS node running to test using localhost.

``` bash
./vendor/bin/pest
```

## Contributing

Please see [contributing.md](contributing.md) for details and a todolist.

## Security

If you discover any security related issues, please email author@email.com instead of using the issue tracker.

## Credits

- [Christian Pavilonis][link-author]
- [All Contributors][link-contributors]

## License

MIT. Please see the [license file](license.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/fliq/ipfs.svg?style=flat-square

[ico-downloads]: https://img.shields.io/packagist/dt/fliq/ipfs.svg?style=flat-square

[ico-travis]: https://img.shields.io/travis/fliq/ipfs/master.svg?style=flat-square

[ico-styleci]: https://styleci.io/repos/12345678/shield

[link-packagist]: https://packagist.org/packages/fliq/ipfs

[link-downloads]: https://packagist.org/packages/fliq/ipfs

[link-travis]: https://travis-ci.org/fliq/ipfs

[link-styleci]: https://styleci.io/repos/12345678

[link-author]: https://github.com/ChristianPavilonis

[link-contributors]: ../../contributors

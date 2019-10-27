The HTTP client for humans.

[![Build Status](https://img.shields.io/travis/com/mnapoli/bof/master.svg?style=flat-square)](https://travis-ci.com/mnapoli/bof)
[![Latest Version](https://img.shields.io/github/release/mnapoli/bof.svg?style=flat-square)](https://packagist.org/packages/mnapoli/bof)

![](img/logo.png)

## Why?

Bof is a HTTP client meant to be as user friendly as possible.

It makes the most classic use cases, such as downloading a file, interacting with a JSON API or submitting a form, as simple as possible.

Since Bof is based on [Guzzle](http://docs.guzzlephp.org/en/stable/overview.html), more advanced use cases can be addressed by using Guzzle's methods directly.

To sum up, Bof:

- is user friendly
- avoids magic strings and arrays for configuration: instead it provides explicit, typed and documented methods that can be autocompleted by IDEs
- comes with sane defaults: JSON is supported natively, 4xx and 5xx responses throw exceptions, timeouts are short by default
- is PSR-7 compliant

Future plans:

- PSR-18 compliance (the HTTP client standard)
- resiliency mechanisms such as retry, backoff, etc.

Want a short illustration? Here is Bof compared to Guzzle:

```php
// Bof
$http = new Bof\Http;
$createdProduct = $http
    ->withHeader('Authorization', 'Token abcd')
    ->postJson('https://example.com/api/products', [
        'Hello' => 'world',
    ])
    ->getData();

// Guzzle
$client = new GuzzleHttp\Client([
    'headers' => [
        'Authorization' => 'Token abcd',
    ],
]);
$response = $client->request('POST', 'https://example.com/api/products', [
   'json' => [
        'Hello' => 'world',
   ]
]);
$createdProduct = json_decode($response->getBody()->__toString(), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    throw new Exception('There was an error while decoding the JSON response');
}
```

## Do we need a new HTTP client?

Probably not. If this client attracts interest, that may mean that our already popular HTTP clients could use a simpler API targeting the simple use cases. If you maintain a HTTP client and are interested, I would love to merge Bof into existing libraries. Open an issue!

## Installation

```bash
composer require mnapoli/bof
```

## Usage

```php
$http = new Bof\Http;

$response = $http->get('https://example.com/api/products');
```

### Configuration

**The `Bof\Http` class is immutable**.

Configuration is applied by calling `withXxx()` methods which create a new object every time:

```php
$http = new Bof\Http;

// The header will apply to all subsequent requests
$http = $http->withHeader('Authorization', "Bearer $token");
```

Remember that `withXxx()` methods return *a copy* of the original client:

```php
$http1 = new Bof\Http;

$http2 = $http1->withHeader('Authorization', "Bearer $token");

// $http1 does not have the header applied
// $http2 has the header
```

Thanks to that pattern, the same methods can be used to apply configuration only for a specific request:

```php
$products = $http->withHeader('Authorization', "Bearer $token")
    ->get('https://example.com/api/products')
    ->getData();

// The next requests will *not* have the `Authorization` header
```

### Responses

Responses are PSR-7 compliant. They also provide methods to facilitate working with JSON responses:

```php
$http = new Bof\Http;

$products = $http->get('https://example.com/api/products')
    ->getData();
```

The `getData()` method will decode the JSON response.

All PSR-7 methods are also available:

```php
$response = $http->get('https://example.com/api/products');
echo $response->getStatusCode();
echo $response->getHeader('Content-Length')[0];
echo $response->getBody()->getContents();
```

[Learn more](http://docs.guzzlephp.org/en/stable/quickstart.html#using-responses).

### Sending JSON data

Using the JSON methods, the data will automatically encoded to JSON. A `Content-Type` header of `application/json` will be added.

```php
$http->postJson('https://example.com/api/products', [
    'foo' => 'bar',
]);
// putJson() or patchJson() works as well
```

### Sending form data

Data can also be sent as a `application/x-www-form-urlencoded` POST request:

```php
$http->postForm('https://example.com/api/products', [
    'foo' => 'bar',
    'baz' => ['hi', 'there!'],
]);
// putForm() works as well
```

### Exceptions

Invalid HTTP responses (status code 4xx or 5xx) will throw exceptions.

```php
try {
    $http->get('https://example.com/api/products');
} catch (\GuzzleHttp\Exception\GuzzleException $e) {
    // $e->getRequest()
    // $e->getResponse()
    ...
}
```

[Learn more](http://docs.guzzlephp.org/en/stable/quickstart.html#exceptions).

### Headers

```php
$http = $http->withHeader('Authorization', "Bearer $token");

// Headers can have multiple values
$http = $http->withHeader('X-Foo', ['Bar', 'Baz']);
```

### Timeouts

Timeouts are set at short values by default:

- 5 seconds for the request timeout
- 3 seconds for the HTTP connection timeout

You can set shorter or longer timeouts (or disable them by setting them at `0`):

```php
// 2 seconds for the request timeout, 1 second for the connection timeout
$http = $http->withTimeout(2, 1);
```

### Query string parameters

You can set query string parameters in the request's URI:

$response = $http->get('http://httpbin.org?foo=bar');

You can specify the query string parameters as an array:

```php
$http->withQueryParams(['foo' => 'bar'])
    ->get('http://httpbin.org');
```

Providing the option as an array will use PHP's `http_build_query` function to format the query string.

And finally, you can provide the query request option as a string.

```php
$http->withQueryParams('foo=bar')
    ->get('http://httpbin.org');
```

### Proxy

Use `withSingleProxy()` to specify a proxy for all protocols:

```php
$http = $http->withSingleProxy('tcp://localhost:8125');
```

Use `withMultipleProxies()` to specify a different proxy for HTTP and HTTPS, as well as a list of host names that should not be proxied to:

```php
$http = $http->withMultipleProxies(
    'tcp://localhost:8125', // Use this proxy with HTTP 
    'tcp://localhost:9124', // Use this proxy with HTTPS
    ['.mit.edu', 'foo.com'] // Don't use a proxy with these
);
```

Note that you can provide proxy URLs that contain a scheme, username, and password. For example, `http://username:password@192.168.16.1:10`.

## Guzzle integration

Bof is based on Guzzle. You can even make it use your own Guzzle client, for example if you preconfigured it:

```php
$guzzleClient = new GuzzleHttp\Client([
    'base_uri' => 'http://httpbin.org',
    'timeout'  => 2.0,
]);

$http = new Bof\Http($guzzleClient);
```

[Learn more](http://docs.guzzlephp.org/en/stable/request-options.html).

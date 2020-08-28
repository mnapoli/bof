<?php /** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

namespace Bof\Test;

use Bof\Http;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class HttpTest extends TestCase
{
    public function test get(): void
    {
        $http = Http::mock([
            new Response(200, ['X-Foo' => 'Bar'], 'OK!'),
        ]);
        $response = $http->get('https://example.com');
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('OK!', $response->getBodyAsString());
        $this->assertSame('Bar', $response->getHeaderLine('X-Foo'));
    }

    public function test delete(): void
    {
        $http = Http::mock([
            new Response(200),
        ]);
        $response = $http->delete('https://example.com');
        $this->assertSame(200, $response->getStatusCode());
    }

    public function test post json(): void
    {
        $http = Http::mock([
            static function (RequestInterface $request): ResponseInterface {
                $headers = ['X-Encoding' => $request->getHeaderLine('Content-Type')];
                return new Response(200, $headers, (string) $request->getBody());
            },
        ]);

        $response = $http->postJson('https://example.com', [
            'foo' => 'bar',
        ]);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('{"foo":"bar"}', $response->getBodyAsString());
        // Check the header was added automatically
        $this->assertSame('application/json', $response->getHeaderLine('X-Encoding'));
        // Check the JSON can be decoded
        $this->assertEquals(['foo' => 'bar'], $response->getData());
    }

    public function test put json(): void
    {
        $http = Http::mock([
            static function (RequestInterface $request): ResponseInterface {
                $headers = ['X-Encoding' => $request->getHeaderLine('Content-Type')];
                return new Response(200, $headers, (string) $request->getBody());
            },
        ]);

        $response = $http->putJson('https://example.com', [
            'foo' => 'bar',
        ]);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('{"foo":"bar"}', $response->getBodyAsString());
        // Check the header was added automatically
        $this->assertSame('application/json', $response->getHeaderLine('X-Encoding'));
        // Check the JSON can be decoded
        $this->assertEquals(['foo' => 'bar'], $response->getData());
    }

    public function test patch json(): void
    {
        $http = Http::mock([
            static function (RequestInterface $request): ResponseInterface {
                $headers = ['X-Encoding' => $request->getHeaderLine('Content-Type')];
                return new Response(200, $headers, (string) $request->getBody());
            },
        ]);

        $response = $http->patchJson('https://example.com', [
            'foo' => 'bar',
        ]);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('{"foo":"bar"}', $response->getBodyAsString());
        // Check the header was added automatically
        $this->assertSame('application/json', $response->getHeaderLine('X-Encoding'));
        // Check the JSON can be decoded
        $this->assertEquals(['foo' => 'bar'], $response->getData());
    }

    public function test post form data(): void
    {
        $http = Http::mock([
            static function (RequestInterface $request): ResponseInterface {
                $headers = ['X-Encoding' => $request->getHeaderLine('Content-Type')];
                return new Response(200, $headers, (string) $request->getBody());
            },
        ]);

        $response = $http->postForm('https://example.com', [
            'foo' => 'bar',
            'baz' => ['hi', 'there!'],
        ]);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('foo=bar&baz%5B0%5D=hi&baz%5B1%5D=there%21', $response->getBodyAsString());
        // Check the header was added automatically
        $this->assertSame('application/x-www-form-urlencoded', $response->getHeaderLine('X-Encoding'));
    }

    public function test put form data(): void
    {
        $http = Http::mock([
            static function (RequestInterface $request): ResponseInterface {
                $headers = ['X-Encoding' => $request->getHeaderLine('Content-Type')];
                return new Response(200, $headers, (string) $request->getBody());
            },
        ]);

        $response = $http->putForm('https://example.com', [
            'foo' => 'bar',
            'baz' => ['hi', 'there!'],
        ]);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('foo=bar&baz%5B0%5D=hi&baz%5B1%5D=there%21', $response->getBodyAsString());
        // Check the header was added automatically
        $this->assertSame('application/x-www-form-urlencoded', $response->getHeaderLine('X-Encoding'));
    }

    public function test with header(): void
    {
        $http1 = Http::mock([
            function (RequestInterface $request, array $options): ResponseInterface {
                $this->assertEquals(['Bar', 'Baz'], $request->getHeader('X-Foo'));
                return new Response(200);
            },
        ]);
        $http2 = $http1->withHeader('X-Foo', ['Bar', 'Baz']);
        $this->assertNotSame($http1, $http2); // Check that a copy is created
        $http2->get('https://example.com'); // Trigger the request to check that the options are set
    }

    public function test with timeout(): void
    {
        $http1 = Http::mock([
            function (RequestInterface $request, array $options): ResponseInterface {
                $this->assertSame(1., $options['timeout']);
                $this->assertSame(2., $options['connect_timeout']);
                return new Response(200);
            },
        ]);
        $http2 = $http1->withTimeout(1, 2);
        $this->assertNotSame($http1, $http2); // Check that a copy is created
        $http2->get('https://example.com'); // Trigger the request to check that the options are set
    }

    public function test with query params as array(): void
    {
        $http1 = Http::mock([
            function (RequestInterface $request, array $options): ResponseInterface {
                $this->assertSame('foo=bar', $request->getUri()->getQuery());
                return new Response(200);
            },
        ]);
        $http2 = $http1->withQueryParams(['foo' => 'bar']);
        $this->assertNotSame($http1, $http2); // Check that a copy is created
        $http2->get('https://example.com'); // Trigger the request to check that the options are set
    }

    public function test with query params as string(): void
    {
        $http1 = Http::mock([
            function (RequestInterface $request, array $options): ResponseInterface {
                $this->assertSame('foo=bar', $request->getUri()->getQuery());
                return new Response(200);
            },
        ]);
        $http2 = $http1->withQueryParams('foo=bar');
        $this->assertNotSame($http1, $http2); // Check that a copy is created
        $http2->get('https://example.com'); // Trigger the request to check that the options are set
    }

    public function test with single proxy(): void
    {
        $http1 = Http::mock([
            function (RequestInterface $request, array $options): ResponseInterface {
                $this->assertSame('tcp://localhost:8125', $options['proxy']);
                return new Response(200);
            },
        ]);
        $http2 = $http1->withSingleProxy('tcp://localhost:8125');
        $this->assertNotSame($http1, $http2); // Check that a copy is created
        $http2->get('https://example.com'); // Trigger the request to check that the options are set
    }

    public function test with multiple proxies(): void
    {
        $http1 = Http::mock([
            function (RequestInterface $request, array $options): ResponseInterface {
                $this->assertEquals([
                    'http' => 'tcp://localhost:8125',
                    'https' => 'tcp://localhost:9124',
                    'no' => ['.mit.edu', 'foo.com'],
                ], $options['proxy']);
                return new Response(200);
            },
        ]);
        $http2 = $http1->withMultipleProxies('tcp://localhost:8125', 'tcp://localhost:9124', ['.mit.edu', 'foo.com']);
        $this->assertNotSame($http1, $http2); // Check that a copy is created
        $http2->get('https://example.com'); // Trigger the request to check that the options are set
    }
}

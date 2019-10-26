<?php declare(strict_types=1);

namespace Bof;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\RequestOptions;

class Http
{
    /** @var ClientInterface */
    private $client;
    /** @var array<string, string|array> */
    private $headers = [];
    /** @var float number of seconds to wait while waiting for the response. Use 0 to wait indefinitely. */
    private $requestTimeout = 5.;
    /** @var float Number of seconds to wait while trying to connect to the server. Use 0 to wait indefinitely. */
    private $connectionTimeout = 3.;
    /** @var string|array<string, string|array>|null */
    private $proxy;
    /** @var string|array|null */
    private $queryParams;

    public function __construct(?ClientInterface $client = null)
    {
        $this->client = $client ?: new Client;
    }

    public static function mock(array $expectedResponses): self
    {
        $mock = new MockHandler($expectedResponses);
        $guzzle = new Client([
            'handler' => HandlerStack::create($mock),
        ]);
        return new self($guzzle);
    }

    /**
     * @throws GuzzleException
     */
    public function get(string $url): HttpResponse
    {
        $response = $this->client->request('GET', $url, $this->requestOptions());
        return HttpResponse::fromGuzzleResponse($response);
    }

    /**
     * @throws GuzzleException
     */
    public function delete(string $url): HttpResponse
    {
        $response = $this->client->request('DELETE', $url, $this->requestOptions());
        return HttpResponse::fromGuzzleResponse($response);
    }

    /**
     * @param mixed $data
     * @throws GuzzleException
     */
    public function postJson(string $url, $data): HttpResponse
    {
        return $this->sendJson('POST', $url, $data);
    }

    /**
     * @param mixed $data
     * @throws GuzzleException
     */
    public function putJson(string $url, $data): HttpResponse
    {
        return $this->sendJson('PUT', $url, $data);
    }

    /**
     * @param mixed $data
     * @throws GuzzleException
     */
    public function patchJson(string $url, $data): HttpResponse
    {
        return $this->sendJson('PATCH', $url, $data);
    }

    /**
     * @param array<array-key,mixed> $data
     * @throws GuzzleException
     */
    public function postForm(string $url, array $data): HttpResponse
    {
        return $this->sendForm('POST', $url, $data);
    }

    /**
     * @param array<array-key,mixed> $data
     * @throws GuzzleException
     */
    public function putForm(string $url, array $data): HttpResponse
    {
        return $this->sendForm('PUT', $url, $data);
    }

    /**
     * @param string|array<int,string> $value
     */
    public function withHeader(string $name, $value): self
    {
        $http = clone $this;
        $http->headers[$name] = $value;
        return $http;
    }

    public function withTimeout(float $timeout, float $connectionTimeout): self
    {
        $http = clone $this;
        $http->requestTimeout = $timeout;
        $http->connectionTimeout = $connectionTimeout;
        return $http;
    }

    public function withSingleProxy(string $proxy): self
    {
        $http = clone $this;
        $http->proxy = $proxy;
        return $http;
    }

    public function withMultipleProxies(string $httpProxy, string $httpsProxy, array $notProxiedDomains): self
    {
        $http = clone $this;
        $http->proxy = [
            'http'  => $httpProxy, // Use this proxy with "http"
            'https' => $httpsProxy, // Use this proxy with "https"
            'no' => $notProxiedDomains, // Don't use a proxy with these domains
        ];
        return $http;
    }

    /**
     * @param string|array $queryParams
     */
    public function withQueryParams($queryParams): self
    {
        $http = clone $this;
        $http->queryParams = $queryParams;
        return $http;
    }

    /**
     * @param mixed $data
     * @throws GuzzleException
     */
    private function sendJson(string $method, string $url, $data): HttpResponse
    {
        $requestOptions = $this->requestOptions();
        $requestOptions[RequestOptions::JSON] = $data;
        $response = $this->client->request($method, $url, $requestOptions);

        return HttpResponse::fromGuzzleResponse($response);
    }

    /**
     * @param array<array-key, mixed> $data
     * @throws GuzzleException
     */
    private function sendForm(string $method, string $url, array $data): HttpResponse
    {
        $requestOptions = $this->requestOptions();
        $requestOptions[RequestOptions::FORM_PARAMS] = $data;
        $response = $this->client->request($method, $url, $requestOptions);

        return HttpResponse::fromGuzzleResponse($response);
    }

    /**
     * @return array<string,mixed>
     * @psalm-mutation-free
     */
    private function requestOptions(): array
    {
        return [
            RequestOptions::CONNECT_TIMEOUT => $this->connectionTimeout,
            RequestOptions::HEADERS => $this->headers,
            RequestOptions::PROXY => $this->proxy,
            RequestOptions::QUERY => $this->queryParams,
            RequestOptions::TIMEOUT => $this->requestTimeout,
        ];
    }
}

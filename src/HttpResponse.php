<?php declare(strict_types=1);

namespace Bof;

use JsonException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class HttpResponse implements ResponseInterface
{
    /** @var ResponseInterface */
    private $wrappedResponse;

    public function __construct(ResponseInterface $wrappedResponse)
    {
        $this->wrappedResponse = $wrappedResponse;
    }

    public function getBodyAsString(): string
    {
        return (string) $this->getBody();
    }

    /**
     * @throws JsonException When decoding JSON fails.
     */
    public function getData(): array
    {
        return $this->decodeJson($this->getBodyAsString());
    }

    public function getProtocolVersion(): string
    {
        return $this->wrappedResponse->getProtocolVersion();
    }

    public function withProtocolVersion($version)
    {
        return new self($this->wrappedResponse->withProtocolVersion($version));
    }

    public function getHeaders(): array
    {
        return $this->wrappedResponse->getHeaders();
    }

    public function hasHeader($name): bool
    {
        return $this->wrappedResponse->hasHeader($name);
    }

    public function getHeader($name): array
    {
        return $this->wrappedResponse->getHeader($name);
    }

    public function getHeaderLine($name): string
    {
        return $this->wrappedResponse->getHeaderLine($name);
    }

    public function withHeader($name, $value): self
    {
        return new self($this->wrappedResponse->withHeader($name, $value));
    }

    public function withAddedHeader($name, $value): self
    {
        return new self($this->wrappedResponse->withAddedHeader($name, $value));
    }

    public function withoutHeader($name): self
    {
        return new self($this->wrappedResponse->withoutHeader($name));
    }

    public function getBody(): StreamInterface
    {
        return $this->wrappedResponse->getBody();
    }

    public function withBody(StreamInterface $body): self
    {
        return new self($this->wrappedResponse->withBody($body));
    }

    public function getStatusCode(): int
    {
        return $this->wrappedResponse->getStatusCode();
    }

    public function withStatus($code, $reasonPhrase = ''): self
    {
        return new self($this->wrappedResponse->withStatus($code, $reasonPhrase));
    }

    public function getReasonPhrase(): string
    {
        return $this->wrappedResponse->getReasonPhrase();
    }

    /**
     * @return mixed
     * @throws JsonException When decoding JSON fails.
     */
    private function decodeJson(string $json)
    {
        return \json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    }
}

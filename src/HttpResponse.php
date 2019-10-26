<?php declare(strict_types=1);

namespace Bof;

use GuzzleHttp\Psr7\Response;
use JsonException;

class HttpResponse extends Response
{
    public static function fromGuzzleResponse(Response $guzzleResponse): self
    {
        return new self(
            $guzzleResponse->getStatusCode(),
            $guzzleResponse->getHeaders(),
            $guzzleResponse->getBody(),
            $guzzleResponse->getProtocolVersion(),
            $guzzleResponse->getReasonPhrase(),
        );
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

    /**
     * @return mixed
     * @throws JsonException When decoding JSON fails.
     */
    private function decodeJson(string $json)
    {
        return \json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    }
}

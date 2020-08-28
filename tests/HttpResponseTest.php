<?php /** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

namespace Bof\Test;

use Bof\HttpResponse;
use PHPUnit\Framework\TestCase;

class HttpResponseTest extends TestCase
{
    public function test get body as string(): void
    {
        $response = new HttpResponse(200, [], 'foo');
        $this->assertSame('foo', $response->getBodyAsString());
    }

    public function test get body as string always rewinds the stream(): void
    {
        $response = new HttpResponse(200, [], 'foo');

        $response->getBody()->getContents(); // this will move the cursor of the stream to the end
        $this->assertSame('', $response->getBody()->getContents()); // we check that: the content returned is now empty

        // Yet `getBodyAsString()` always works
        $this->assertSame('foo', $response->getBodyAsString());
    }

    public function test get data decodes JSON(): void
    {
        $response = new HttpResponse(200, [], json_encode(['foo' => 'bar'], JSON_THROW_ON_ERROR));
        $this->assertEquals(['foo' => 'bar'], $response->getData());
    }

    public function test get data throws on invalid JSON(): void
    {
        $response = new HttpResponse(200, [], 'foobar');

        $this->expectException(\JsonException::class);
        $response->getData();
    }
}

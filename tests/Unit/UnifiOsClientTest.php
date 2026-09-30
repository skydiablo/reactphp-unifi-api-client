<?php

declare(strict_types=1);

namespace SkyDiablo\UnifiApiClient\Tests\Unit;

use Mockery;
use Mockery\MockInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use React\Http\Browser;
use React\Promise\Promise;
use ReflectionClass;
use SkyDiablo\UnifiApiClient\ApiEndpoint;
use SkyDiablo\UnifiApiClient\Tests\TestCase;
use SkyDiablo\UnifiApiClient\UnifiOsClient;

class UnifiOsClientTest extends TestCase
{
    private MockInterface $browserMock;
    private UnifiOsClient $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->browserMock = Mockery::mock(Browser::class);

        $this->client = new class('https://uos.example.com:11443', 'test-api-key', null, $this->browserMock) extends UnifiOsClient {
            public function __construct(
                string $uri,
                string $apiKey,
                $connector = null,
                $browser = null
            ) {
                parent::__construct($uri, $apiKey, $connector);
                if ($browser) {
                    $this->httpClient = $browser;
                }
            }

            public function getDefaultHeader(array $header = []): array
            {
                return $this->defaultHeader($header);
            }

            public function getUrl(ApiEndpoint $endpoint, array $pathParams = []): UriInterface
            {
                return $this->url($endpoint, $pathParams);
            }

            public function getBuildBaseUri(string $uri): string
            {
                return $this->buildBaseUri($uri);
            }
        };
    }

    public function testBuildBaseUriAddsProxyNetworkPrefix(): void
    {
        $this->assertSame(
            'https://uos.example.com:11443/proxy/network/',
            $this->client->getBuildBaseUri('https://uos.example.com:11443')
        );
        $this->assertSame(
            'https://uos.example.com:11443/proxy/network/',
            $this->client->getBuildBaseUri('https://uos.example.com:11443/')
        );
    }

    public function testDefaultHeaderContainsApiKey(): void
    {
        $headers = $this->client->getDefaultHeader();

        $this->assertSame('test-api-key', $headers['X-API-Key']);
        $this->assertArrayNotHasKey('Cookie', $headers);
        $this->assertArrayNotHasKey('X-Csrf-Token', $headers);
    }

    public function testUrlKeepsLegacyEndpointPaths(): void
    {
        $url = $this->client->getUrl(ApiEndpoint::DEVICE_BASICS, ['site' => 'default']);

        $this->assertEquals('api/s/default/stat/device-basic', $url->getPath());
    }

    public function testGetDoesNotAttemptLogin(): void
    {
        $streamMock = Mockery::mock(StreamInterface::class);
        $streamMock->shouldReceive('getContents')->andReturn(json_encode(['data' => [['name' => 'default']]]));

        $responseMock = Mockery::mock(ResponseInterface::class);
        $responseMock->shouldReceive('getHeader')->with('Content-Type')->andReturn(['application/json']);
        $responseMock->shouldReceive('getBody')->andReturn($streamMock);
        $responseMock->shouldReceive('getHeader')->with('Set-Cookie')->andReturn([]);

        $this->browserMock->shouldReceive('get')
            ->once()
            ->andReturn(new Promise(function ($resolve) use ($responseMock) {
                $resolve($responseMock);
            }));

        $promise = $this->client->get(ApiEndpoint::SITES);
        $promise->then(function ($result) {
            $this->assertEquals(['data' => [['name' => 'default']]], $result);
        });
    }

    public function testLogoutIsNoOp(): void
    {
        $resolved = null;
        $this->client->logout()->then(function ($result) use (&$resolved) {
            $resolved = $result;
        });

        $this->assertSame([], $resolved);
    }

    public function testShouldAutoLoginIsDisabled(): void
    {
        $reflection = new ReflectionClass(UnifiOsClient::class);
        $method = $reflection->getMethod('shouldAutoLogin');

        $this->assertFalse($method->invoke($this->client));
    }
}

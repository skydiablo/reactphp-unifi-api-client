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
use React\Promise\PromiseInterface;
use SkyDiablo\UnifiApiClient\ApiEndpoint;
use SkyDiablo\UnifiApiClient\Tests\TestCase;
use SkyDiablo\UnifiApiClient\UnifiClient;

class UnifiClientTest extends TestCase
{
    private MockInterface $browserMock;
    private UnifiClient $client;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock Browser
        $this->browserMock = Mockery::mock(Browser::class);
        $this->browserMock->shouldReceive('withBase')->andReturn($this->browserMock);
        
        // Create client with mocked browser
        $this->client = new class('https://unifi.example.com', 'username', 'password', null, $this->browserMock) extends UnifiClient {
            public function __construct(
                string $uri,
                string $username,
                string $password,
                $connector = null,
                $browser = null
            ) {
                parent::__construct($uri, $username, $password, $connector);
                if ($browser) {
                    $this->httpClient = $browser;
                }
            }
            
            // Expose protected methods for testing
            public function getDefaultHeader(array $header = []): array
            {
                return $this->defaultHeader($header);
            }
            
            public function getUrl(ApiEndpoint $endpoint, array $pathParams = []): UriInterface
            {
                return $this->url($endpoint, $pathParams);
            }
            
            public function getBody($data): false|string
            {
                return $this->body($data);
            }
            
            public function getDecode(ResponseInterface $response): array
            {
                return $this->decode($response);
            }
            
            public function getAddQueryParams(array $queryParams, UriInterface $uri): UriInterface
            {
                return $this->addQueryParams($queryParams, $uri);
            }
        };
    }

    public function testDefaultHeader(): void
    {
        $headers = $this->client->getDefaultHeader();
        
        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertArrayHasKey('Accept', $headers);
        $this->assertArrayHasKey('Cache-Control', $headers);
        $this->assertEquals('application/json;charset=utf-8', $headers['Content-Type']);
    }
    
    public function testUrl(): void
    {
        $url = $this->client->getUrl(ApiEndpoint::DEVICE_BASICS, ['site' => 'default']);
        
        $this->assertEquals('api/s/default/stat/device-basic', $url->getPath());
    }
    
    public function testBody(): void
    {
        $data = ['key' => 'value'];
        $body = $this->client->getBody($data);
        
        $this->assertEquals(json_encode($data), $body);
    }
    
    public function testGet(): void
    {
        // Mock response
        $streamMock = Mockery::mock(StreamInterface::class);
        $streamMock->shouldReceive('getContents')->andReturn(json_encode(['data' => ['result' => 'success']]));
        
        $responseMock = Mockery::mock(ResponseInterface::class);
        $responseMock->shouldReceive('getHeader')->with('Content-Type')->andReturn(['application/json']);
        $responseMock->shouldReceive('getBody')->andReturn($streamMock);
        $responseMock->shouldReceive('getHeader')->with('Set-Cookie')->andReturn([]);
        
        // Setup browser mock to return the response
        $this->browserMock->shouldReceive('get')
            ->once()
            ->andReturn(new Promise(function ($resolve) use ($responseMock) {
                $resolve($responseMock);
            }));
        
        // Call get method
        $promise = $this->client->get(ApiEndpoint::INFO);
        
        // Test promise resolves with expected data
        $promise->then(function ($result) {
            $this->assertEquals(['data' => ['result' => 'success']], $result);
        });
    }
    
    public function testPost(): void
    {
        // Mock response
        $streamMock = Mockery::mock(StreamInterface::class);
        $streamMock->shouldReceive('getContents')->andReturn(json_encode(['data' => ['result' => 'success']]));
        
        $responseMock = Mockery::mock(ResponseInterface::class);
        $responseMock->shouldReceive('getHeader')->with('Content-Type')->andReturn(['application/json']);
        $responseMock->shouldReceive('getBody')->andReturn($streamMock);
        $responseMock->shouldReceive('getHeader')->with('Set-Cookie')->andReturn([]);
        
        // Setup browser mock to return the response
        $this->browserMock->shouldReceive('post')
            ->once()
            ->andReturn(new Promise(function ($resolve) use ($responseMock) {
                $resolve($responseMock);
            }));
        
        // Call post method
        $promise = $this->client->post(['param' => 'value'], ApiEndpoint::LOGIN);
        
        // Test promise resolves with expected data
        $promise->then(function ($result) {
            $this->assertEquals(['data' => ['result' => 'success']], $result);
        });
    }
    
    public function testParseSetCookies(): void
    {
        $responseMock = Mockery::mock(ResponseInterface::class);
        $responseMock->shouldReceive('getHeader')
            ->with('Set-Cookie')
            ->andReturn([
                'unifises=abcdef123456; path=/; HttpOnly; SameSite=Strict',
                'csrf_token=xyz789; path=/; HttpOnly; SameSite=Strict'
            ]);
        
        // Create a reflection to access private properties
        $reflectionClass = new \ReflectionClass(UnifiClient::class);
        $unifiSessionProperty = $reflectionClass->getProperty('unifiSession');
        $unifiSessionProperty->setAccessible(true);
        $csrfTokenProperty = $reflectionClass->getProperty('csrfToken');
        $csrfTokenProperty->setAccessible(true);
        
        // Call parseSetCookies
        $this->client->parseSetCookies($responseMock);
        
        // Check if properties were set correctly
        $this->assertEquals('abcdef123456', $unifiSessionProperty->getValue($this->client));
        $this->assertEquals('xyz789', $csrfTokenProperty->getValue($this->client));
    }
    
    public function testLogout(): void
    {
        // Mock response
        $streamMock = Mockery::mock(StreamInterface::class);
        $streamMock->shouldReceive('getContents')->andReturn(json_encode(['data' => ['result' => 'success']]));
        
        $responseMock = Mockery::mock(ResponseInterface::class);
        $responseMock->shouldReceive('getHeader')->with('Content-Type')->andReturn(['application/json']);
        $responseMock->shouldReceive('getBody')->andReturn($streamMock);
        $responseMock->shouldReceive('getHeader')->with('Set-Cookie')->andReturn([]);
        
        // Setup browser mock to return the response
        $this->browserMock->shouldReceive('post')
            ->once()
            ->andReturn(new Promise(function ($resolve) use ($responseMock) {
                $resolve($responseMock);
            }));
        
        // Call logout method
        $promise = $this->client->logout();
        
        // Test promise resolves with expected data and session is cleared
        $promise->then(function ($result) {
            $this->assertEquals(['data' => ['result' => 'success']], $result);
            
            // Create a reflection to access private properties
            $reflectionClass = new \ReflectionClass(UnifiClient::class);
            $unifiSessionProperty = $reflectionClass->getProperty('unifiSession');
            $unifiSessionProperty->setAccessible(true);
            $csrfTokenProperty = $reflectionClass->getProperty('csrfToken');
            $csrfTokenProperty->setAccessible(true);
            
            $this->assertNull($unifiSessionProperty->getValue($this->client));
            $this->assertNull($csrfTokenProperty->getValue($this->client));
        });
    }
} 
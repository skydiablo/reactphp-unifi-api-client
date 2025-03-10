<?php

declare(strict_types=1);

namespace SkyDiablo\UnifiApiClient\Tests\Integration;

use React\EventLoop\Loop;
use SkyDiablo\UnifiApiClient\ApiEndpoint;
use SkyDiablo\UnifiApiClient\Services\BasicService;
use SkyDiablo\UnifiApiClient\Services\Device;
use SkyDiablo\UnifiApiClient\Services\Site;
use SkyDiablo\UnifiApiClient\Tests\TestCase;
use SkyDiablo\UnifiApiClient\UnifiClient;

/**
 * Diese Tests sind für die Integration mit einer echten Unifi-Controller-Instanz gedacht.
 * Sie werden standardmäßig übersprungen, es sei denn, die Umgebungsvariablen sind gesetzt.
 */
class UnifiClientIntegrationTest extends TestCase
{
    private ?UnifiClient $client = null;
    private ?BasicService $basicService = null;
    private ?Site $siteService = null;
    private ?Device $deviceService = null;

    protected function setUp(): void
    {
        parent::setUp();

        // Überspringe Tests, wenn keine Umgebungsvariablen gesetzt sind
        if (!getenv('UNIFI_HOST') || !getenv('UNIFI_USERNAME') || !getenv('UNIFI_PASSWORD')) {
            $this->markTestSkipped('Integration tests require UNIFI_HOST, UNIFI_USERNAME and UNIFI_PASSWORD environment variables');
        }

        $this->client = new UnifiClient(
            getenv('UNIFI_HOST'),
            getenv('UNIFI_USERNAME'),
            getenv('UNIFI_PASSWORD'),
        );

        $this->basicService = new BasicService($this->client);
        $this->siteService = new Site($this->client);
        $this->deviceService = new Device($this->client);
    }

    protected function tearDown(): void
    {
        if ($this->client) {
            // Logout nach jedem Test
            $this->client->logout()->then(function () {
                Loop::stop();
            });
            Loop::run();
        }

        parent::tearDown();
    }

    public function testLogin(): void
    {
        $promise = $this->client->get(ApiEndpoint::INFO);

        $result = null;
        $promise->then(function ($data) use (&$result) {
            $result = $data;
            Loop::stop();
        });

        Loop::run();

        $this->assertIsArray($result);
        $this->assertNotCount(0, $result);
    }

    public function testGetInfo(): void
    {
        $promise = $this->basicService->getInfo();

        $result = null;
        $promise->then(function ($data) use (&$result) {
            $result = $data;
            Loop::stop();
        });

        Loop::run();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('self', $result);
        $this->assertArrayHasKey('sites', $result);
        $this->assertArrayHasKey('system', $result);
        $this->assertArrayHasKey('version', $result['system']);
    }

    public function testGetSites(): void
    {
        $promise = $this->siteService->getSites();

        $result = null;
        $promise->then(function ($data) use (&$result) {
            $result = $data;
            Loop::stop();
        });

        Loop::run();

        $this->assertIsArray($result);
        $this->assertGreaterThan(0, count($result));
        $this->assertArrayHasKey('name', $result[0]);
    }

    public function testGetDeviceBasics(): void
    {
        $promise = $this->deviceService->getDeviceBasics();

        $result = null;
        $promise->then(function ($data) use (&$result) {
            $result = $data;
            Loop::stop();
        });

        Loop::run();

        $this->assertIsArray($result);
    }

    public function testGetDevicesV2(): void
    {
        $promise = $this->deviceService->getDevicesV2();

        $result = null;
        $promise->then(function ($data) use (&$result) {
            $result = $data;
            Loop::stop();
        });

        Loop::run();

        $this->assertIsArray($result);
    }
} 
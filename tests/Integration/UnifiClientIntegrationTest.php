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

        $result = [];
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

        $result = [];
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
        $promise = $this->siteService->get();

        $result = [];
        $promise->then(function ($data) use (&$result) {
            $result = $data;
            Loop::stop();
        });

        Loop::run();

        $this->assertIsArray($result);
        $this->assertGreaterThan(0, count($result));
        $this->assertArrayHasKey('name', $result[0]);
    }

    public function testSiteHealth(): void
    {
        // Hole zuerst die verfügbaren Sites
        $sites = [];
        $this->siteService->get()->then(function ($data) use (&$sites) {
            $sites = $data;
            Loop::stop();
        });
        Loop::run();
        
        $this->assertIsArray($sites);
        $this->assertGreaterThan(0, count($sites));
        
        // Verwende die erste Site für den Health-Test
        $siteName = $sites[0]['name'];
        
        $promise = $this->siteService->health($siteName);

        $result = [];
        $promise->then(function ($data) use (&$result) {
            $result = $data;
            Loop::stop();
        });

        Loop::run();

        $this->assertIsArray($result);
        $this->assertGreaterThan(0, count($result));
        
        // Überprüfe, ob die erwarteten Subsysteme vorhanden sind
        $expectedSubsystems = ['wlan', 'wan', 'www', 'lan', 'vpn'];
        $subsystems = array_column($result, 'subsystem');
        
        foreach ($expectedSubsystems as $expectedSubsystem) {
            $this->assertContains($expectedSubsystem, $subsystems, "$expectedSubsystem subsystem should be present");
        }
        
        // Überprüfe die Struktur jedes Subsystems
        foreach ($result as $subsystem) {
            $this->assertArrayHasKey('subsystem', $subsystem);
            $this->assertArrayHasKey('status', $subsystem);
            
            // Je nach Subsystem-Typ weitere Prüfungen
            switch ($subsystem['subsystem']) {
                case 'wlan':
                    $this->assertArrayHasKey('num_ap', $subsystem);
                    $this->assertArrayHasKey('num_adopted', $subsystem);
                    $this->assertArrayHasKey('num_disabled', $subsystem);
                    $this->assertArrayHasKey('num_disconnected', $subsystem);
                    $this->assertArrayHasKey('num_pending', $subsystem);
                    break;
                    
                case 'wan':
                    $this->assertArrayHasKey('num_gw', $subsystem);
                    $this->assertArrayHasKey('num_adopted', $subsystem);
                    $this->assertArrayHasKey('num_disconnected', $subsystem);
                    $this->assertArrayHasKey('num_pending', $subsystem);
                    break;
                    
                case 'lan':
                    $this->assertArrayHasKey('num_sw', $subsystem);
                    $this->assertArrayHasKey('num_adopted', $subsystem);
                    $this->assertArrayHasKey('num_disconnected', $subsystem);
                    $this->assertArrayHasKey('num_pending', $subsystem);
                    break;
                    
                case 'www':
                case 'vpn':
                    // Diese Subsysteme haben nur den Status
                    break;
            }
        }
    }

    public function testGetDeviceBasics(): void
    {
        $promise = $this->deviceService->getBasics();

        $result = [];
        $promise->then(function ($data) use (&$result) {
            $result = $data;
            Loop::stop();
        });

        Loop::run();

        $this->assertIsArray($result);
    }

    public function testGetDevicesV2(): void
    {
        $promise = $this->deviceService->get();

        $result = [];
        $promise->then(function ($data) use (&$result) {
            $result = $data;
            Loop::stop();
        });

        Loop::run();

        $this->assertIsArray($result);
    }
} 
<?php

declare(strict_types=1);

namespace SkyDiablo\UnifiApiClient\Tests\Unit\Services;

use Mockery;
use React\Promise\Promise;
use SkyDiablo\UnifiApiClient\ApiEndpoint;
use SkyDiablo\UnifiApiClient\Services\Site;
use SkyDiablo\UnifiApiClient\Tests\TestCase;
use SkyDiablo\UnifiApiClient\UnifiClient;

class SiteTest extends TestCase
{
    private $unifiClientMock;
    private Site $siteService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->unifiClientMock = Mockery::mock(UnifiClient::class);
        $this->siteService = new Site($this->unifiClientMock);
    }

    public function testGetSites(): void
    {
        $sitesData = [
            'data' => [
                [
                    '_id' => '6530f107ca649c5968c5fa11',
                    'attr_hidden_id' => 'default',
                    'attr_no_delete' => true,
                    'desc' => 'Default',
                    'device_count' => 0,
                    'name' => 'default',
                    'permissions' => [],
                    'role' => 'admin',
                ],
                [
                    '_id' => '65310c60ca649c5968c5fa3c',
                    'desc' => 'Production',
                    'device_count' => 13,
                    'is_active' => true,
                    'name' => 'erq2fau2',
                    'permissions' => [],
                    'role' => 'admin',
                    'timezone' => 'Europe/Berlin',
                ],
            ]
        ];
        
        $this->unifiClientMock->shouldReceive('get')
            ->once()
            ->with(ApiEndpoint::SITES)
            ->andReturn(new Promise(function ($resolve) use ($sitesData) {
                $resolve($sitesData);
            }));
        
        $promise = $this->siteService->get();
        
        $promise->then(function ($result) use ($sitesData) {
            $this->assertEquals($sitesData['data'], $result);
            $this->assertCount(2, $result);
            
            // Überprüfe die erste Site (Default)
            $this->assertEquals('default', $result[0]['name']);
            $this->assertEquals('Default', $result[0]['desc']);
            $this->assertEquals(0, $result[0]['device_count']);
            $this->assertEquals('admin', $result[0]['role']);
            $this->assertTrue($result[0]['attr_no_delete']);
            
            // Überprüfe die zweite Site (Production)
            $this->assertEquals('erq2fau2', $result[1]['name']);
            $this->assertEquals('Production', $result[1]['desc']);
            $this->assertEquals(13, $result[1]['device_count']);
            $this->assertEquals('admin', $result[1]['role']);
            $this->assertEquals('Europe/Berlin', $result[1]['timezone']);
            $this->assertTrue($result[1]['is_active']);
        });
    }
    
    public function testHealth(): void
    {
        $healthData = [
            'data' => [
                [
                    'subsystem' => 'wlan',
                    'status' => 'unknown',
                    'num_ap' => 0,
                    'num_adopted' => 0,
                    'num_disabled' => 0,
                    'num_disconnected' => 0,
                    'num_pending' => 0,
                ],
                [
                    'subsystem' => 'wan',
                    'num_gw' => 0,
                    'num_adopted' => 0,
                    'num_disconnected' => 0,
                    'num_pending' => 0,
                    'status' => 'unknown',
                ],
                [
                    'subsystem' => 'www',
                    'status' => 'unknown',
                ],
                [
                    'subsystem' => 'lan',
                    'status' => 'unknown',
                    'num_sw' => 0,
                    'num_adopted' => 0,
                    'num_disconnected' => 0,
                    'num_pending' => 0,
                ],
                [
                    'subsystem' => 'vpn',
                    'status' => 'unknown',
                ],
            ]
        ];
        
        $this->unifiClientMock->shouldReceive('get')
            ->once()
            ->with(ApiEndpoint::SITE_HEALTH, ['site' => 'default'])
            ->andReturn(new Promise(function ($resolve) use ($healthData) {
                $resolve($healthData);
            }));
        
        $promise = $this->siteService->health();
        
        $promise->then(function ($result) use ($healthData) {
            $this->assertEquals($healthData['data'], $result);
            $this->assertCount(5, $result);
            
            // Überprüfe, ob alle erwarteten Subsysteme vorhanden sind
            $expectedSubsystems = ['wlan', 'wan', 'www', 'lan', 'vpn'];
            $subsystems = array_column($result, 'subsystem');
            
            foreach ($expectedSubsystems as $expectedSubsystem) {
                $this->assertContains($expectedSubsystem, $subsystems, "$expectedSubsystem subsystem should be present");
            }
            
            // Überprüfe die Struktur jedes Subsystems
            foreach ($result as $subsystem) {
                $this->assertArrayHasKey('subsystem', $subsystem);
                $this->assertArrayHasKey('status', $subsystem);
                $this->assertEquals('unknown', $subsystem['status']);
                
                // Je nach Subsystem-Typ weitere Prüfungen
                switch ($subsystem['subsystem']) {
                    case 'wlan':
                        $this->assertArrayHasKey('num_ap', $subsystem);
                        $this->assertArrayHasKey('num_adopted', $subsystem);
                        $this->assertArrayHasKey('num_disabled', $subsystem);
                        $this->assertArrayHasKey('num_disconnected', $subsystem);
                        $this->assertArrayHasKey('num_pending', $subsystem);
                        $this->assertEquals(0, $subsystem['num_ap']);
                        $this->assertEquals(0, $subsystem['num_adopted']);
                        break;
                        
                    case 'wan':
                        $this->assertArrayHasKey('num_gw', $subsystem);
                        $this->assertArrayHasKey('num_adopted', $subsystem);
                        $this->assertArrayHasKey('num_disconnected', $subsystem);
                        $this->assertArrayHasKey('num_pending', $subsystem);
                        $this->assertEquals(0, $subsystem['num_gw']);
                        $this->assertEquals(0, $subsystem['num_adopted']);
                        break;
                        
                    case 'lan':
                        $this->assertArrayHasKey('num_sw', $subsystem);
                        $this->assertArrayHasKey('num_adopted', $subsystem);
                        $this->assertArrayHasKey('num_disconnected', $subsystem);
                        $this->assertArrayHasKey('num_pending', $subsystem);
                        $this->assertEquals(0, $subsystem['num_sw']);
                        $this->assertEquals(0, $subsystem['num_adopted']);
                        break;
                }
            }
        });
    }
    
    public function testHealthWithCustomSite(): void
    {
        $healthData = [
            'data' => [
                [
                    'subsystem' => 'wlan',
                    'status' => 'unknown',
                    'num_ap' => 0,
                    'num_adopted' => 0,
                    'num_disabled' => 0,
                    'num_disconnected' => 0,
                    'num_pending' => 0,
                ],
                [
                    'subsystem' => 'wan',
                    'num_gw' => 0,
                    'num_adopted' => 0,
                    'num_disconnected' => 0,
                    'num_pending' => 0,
                    'status' => 'unknown',
                ]
            ]
        ];
        
        $this->unifiClientMock->shouldReceive('get')
            ->once()
            ->with(ApiEndpoint::SITE_HEALTH, ['site' => 'custom_site'])
            ->andReturn(new Promise(function ($resolve) use ($healthData) {
                $resolve($healthData);
            }));
        
        $promise = $this->siteService->health('custom_site');
        
        $promise->then(function ($result) use ($healthData) {
            $this->assertEquals($healthData['data'], $result);
            $this->assertCount(2, $result);
            
            // Überprüfe WLAN-Subsystem
            $wlanSubsystem = $result[0];
            $this->assertEquals('wlan', $wlanSubsystem['subsystem']);
            $this->assertEquals('unknown', $wlanSubsystem['status']);
            $this->assertEquals(0, $wlanSubsystem['num_ap']);
            $this->assertEquals(0, $wlanSubsystem['num_adopted']);
            
            // Überprüfe WAN-Subsystem
            $wanSubsystem = $result[1];
            $this->assertEquals('wan', $wanSubsystem['subsystem']);
            $this->assertEquals('unknown', $wanSubsystem['status']);
            $this->assertEquals(0, $wanSubsystem['num_gw']);
            $this->assertEquals(0, $wanSubsystem['num_adopted']);
        });
    }
} 
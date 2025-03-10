<?php

declare(strict_types=1);

namespace SkyDiablo\UnifiApiClient\Tests\Unit\Services;

use Mockery;
use React\Promise\Promise;
use SkyDiablo\UnifiApiClient\ApiEndpoint;
use SkyDiablo\UnifiApiClient\Services\Device;
use SkyDiablo\UnifiApiClient\Tests\TestCase;
use SkyDiablo\UnifiApiClient\UnifiClient;

class DeviceTest extends TestCase
{
    private $unifiClientMock;
    private Device $deviceService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->unifiClientMock = Mockery::mock(UnifiClient::class);
        $this->deviceService = new Device($this->unifiClientMock);
    }

    public function testGetDeviceBasics(): void
    {
        $deviceData = [
            'data' => [
                [
                    '_id' => '6530f2f1ca649c5968c5fa34',
                    'mac' => '00:11:22:33:AA:BB',
                    'name' => 'USW-Pro-24-PoE',
                    'model' => 'USW-Pro-24-PoE',
                    'type' => 'usw',
                    'ip' => '127.0.0.10',
                    'version' => '7.4.156',
                    'adopted' => true,
                    'site_id' => '65310c60ca649c5968c5fa3c',
                    'uptime' => 1463139,
                    'last_seen' => 1697982345,
                    'status' => 'connected',
                    'port_table' => [
                        // Port-Informationen gekürzt...
                    ],
                ],
                [
                    '_id' => '6530f2f1ca649c5968c5fa35',
                    'mac' => 'AA:BB:CC:11:22:33',
                    'name' => 'U6-Pro',
                    'model' => 'U6-Pro',
                    'type' => 'uap',
                    'ip' => '127.0.0.11',
                    'version' => '6.5.83',
                    'adopted' => true,
                    'site_id' => '65310c60ca649c5968c5fa3c',
                    'uptime' => 1462985,
                    'last_seen' => 1697982346,
                    'status' => 'connected',
                    'radio_table' => [
                        // Radio-Informationen gekürzt...
                    ],
                ]
            ]
        ];
        
        $this->unifiClientMock->shouldReceive('get')
            ->once()
            ->with(ApiEndpoint::DEVICE_BASICS, ['site' => 'default'])
            ->andReturn(new Promise(function ($resolve) use ($deviceData) {
                $resolve($deviceData);
            }));
        
        $promise = $this->deviceService->getDeviceBasics();
        
        $promise->then(function ($result) use ($deviceData) {
            $this->assertEquals($deviceData['data'], $result);
            $this->assertCount(2, $result);
            
            // Überprüfe den Switch
            $this->assertEquals('00:11:22:33:AA:BB', $result[0]['mac']);
            $this->assertEquals('USW-Pro-24-PoE', $result[0]['name']);
            $this->assertEquals('usw', $result[0]['type']);
            $this->assertEquals('127.0.0.10', $result[0]['ip']);
            $this->assertEquals('connected', $result[0]['status']);
            
            // Überprüfe den Access Point
            $this->assertEquals('AA:BB:CC:11:22:33', $result[1]['mac']);
            $this->assertEquals('U6-Pro', $result[1]['name']);
            $this->assertEquals('uap', $result[1]['type']);
            $this->assertEquals('127.0.0.11', $result[1]['ip']);
            $this->assertEquals('connected', $result[1]['status']);
        });
    }
    
    public function testGetDeviceBasicsWithCustomSite(): void
    {
        $deviceData = [
            'data' => [
                [
                    '_id' => '6530f2f1ca649c5968c5fa36',
                    'mac' => 'AA:BB:CC:11:22:33',
                    'name' => 'USW-Pro-48-PoE',
                    'model' => 'USW-Pro-48-PoE',
                    'type' => 'usw',
                    'ip' => '127.0.0.2',
                    'version' => '7.4.156',
                    'adopted' => true,
                    'site_id' => '65310c60ca649c5968c5fa3d',
                    'uptime' => 1463139,
                    'last_seen' => 1697982345,
                    'status' => 'connected',
                ]
            ]
        ];
        
        $this->unifiClientMock->shouldReceive('get')
            ->once()
            ->with(ApiEndpoint::DEVICE_BASICS, ['site' => 'custom'])
            ->andReturn(new Promise(function ($resolve) use ($deviceData) {
                $resolve($deviceData);
            }));
        
        $promise = $this->deviceService->getDeviceBasics('custom');
        
        $promise->then(function ($result) use ($deviceData) {
            $this->assertEquals($deviceData['data'], $result);
            $this->assertCount(1, $result);
            $this->assertEquals('AA:BB:CC:11:22:33', $result[0]['mac']);
            $this->assertEquals('USW-Pro-48-PoE', $result[0]['name']);
            $this->assertEquals('usw', $result[0]['type']);
        });
    }
    
    public function testGetDevicesV2(): void
    {
        $deviceData = [
            'data' => [
                [
                    'mac' => '00:11:22:33:AA:BB',
                    'name' => 'USW-Pro-24-PoE',
                    'model' => 'USW-Pro-24-PoE',
                    'type' => 'usw',
                    'ip' => '127.0.0.1',
                    'version' => '7.4.156',
                    'adopted' => true,
                    'site_id' => '65310c60ca649c5968c5fa3c',
                    'status' => [
                        'state' => 'CONNECTED',
                        'uptime' => 1463139,
                        'last_seen' => 1697982345,
                    ],
                    'ports' => [
                        // Port-Informationen gekürzt...
                    ],
                    'stats' => [
                        'system-stats' => [
                            'cpu' => 5.2,
                            'mem' => 32.1,
                            'uptime' => 1463139,
                        ],
                    ],
                ]
            ],
            'meta' => [
                'rc' => 'ok',
            ],
        ];
        
        $this->unifiClientMock->shouldReceive('get')
            ->once()
            ->with(ApiEndpoint::DEVICES_V2, ['site' => 'default'], [])
            ->andReturn(new Promise(function ($resolve) use ($deviceData) {
                $resolve($deviceData);
            }));
        
        $promise = $this->deviceService->getDevicesV2();
        
        $promise->then(function ($result) use ($deviceData) {
            $this->assertEquals($deviceData, $result);
            $this->assertArrayHasKey('data', $result);
            $this->assertArrayHasKey('meta', $result);
            $this->assertEquals('ok', $result['meta']['rc']);
            $this->assertCount(1, $result['data']);
            $this->assertEquals('00:11:22:33:AA:BB', $result['data'][0]['mac']);
            $this->assertEquals('USW-Pro-24-PoE', $result['data'][0]['name']);
            $this->assertEquals('CONNECTED', $result['data'][0]['status']['state']);
        });
    }
    
    public function testGetDevicesV2WithOptions(): void
    {
        $deviceData = [
            'data' => [
                [
                    'mac' => '00:11:22:33:AA:BB',
                    'name' => 'USW-Pro-24-PoE',
                    'model' => 'USW-Pro-24-PoE',
                    'type' => 'usw',
                    'ip' => '127.0.0.1',
                    'version' => '7.4.156',
                    'adopted' => true,
                    'site_id' => '65310c60ca649c5968c5fa3c',
                    'status' => [
                        'state' => 'CONNECTED',
                        'uptime' => 1463139,
                        'last_seen' => 1697982345,
                    ],
                    'ports' => [
                        // Port-Informationen gekürzt...
                    ],
                    'stats' => [
                        'system-stats' => [
                            'cpu' => 5.2,
                            'mem' => 32.1,
                            'uptime' => 1463139,
                        ],
                        'traffic' => [
                            'rx_bytes' => 1234567890,
                            'tx_bytes' => 9876543210,
                        ],
                    ],
                ]
            ],
            'meta' => [
                'rc' => 'ok',
            ],
            'unmanaged' => [
                // Unmanaged Geräte...
            ],
        ];
        
        $this->unifiClientMock->shouldReceive('get')
            ->once()
            ->with(ApiEndpoint::DEVICES_V2, ['site' => 'custom'], [
                'includeTrafficUsage' => true,
                'separateUnmanaged' => true
            ])
            ->andReturn(new Promise(function ($resolve) use ($deviceData) {
                $resolve($deviceData);
            }));
        
        $promise = $this->deviceService->getDevicesV2('custom', true, true);
        
        $promise->then(function ($result) use ($deviceData) {
            $this->assertEquals($deviceData, $result);
            $this->assertArrayHasKey('data', $result);
            $this->assertArrayHasKey('meta', $result);
            $this->assertArrayHasKey('unmanaged', $result);
            $this->assertEquals('ok', $result['meta']['rc']);
            $this->assertCount(1, $result['data']);
            $this->assertEquals('00:11:22:33:AA:BB', $result['data'][0]['mac']);
            $this->assertEquals('USW-Pro-24-PoE', $result['data'][0]['name']);
            $this->assertArrayHasKey('traffic', $result['data'][0]['stats']);
            $this->assertEquals(1234567890, $result['data'][0]['stats']['traffic']['rx_bytes']);
            $this->assertEquals(9876543210, $result['data'][0]['stats']['traffic']['tx_bytes']);
        });
    }
} 
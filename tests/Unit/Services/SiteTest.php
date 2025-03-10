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
        
        $promise = $this->siteService->getSites();
        
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
} 
<?php

declare(strict_types=1);

namespace SkyDiablo\UnifiApiClient\Tests\Unit;

use SkyDiablo\UnifiApiClient\ApiEndpoint;
use SkyDiablo\UnifiApiClient\Tests\TestCase;

class ApiEndpointTest extends TestCase
{
    public function testEndpointValues(): void
    {
        $this->assertEquals('/api/login', ApiEndpoint::LOGIN->value);
        $this->assertEquals('/api/logout', ApiEndpoint::LOGOUT->value);
        $this->assertEquals('/v2/api/info', ApiEndpoint::INFO->value);
        $this->assertEquals('/api/s/{site}/stat/device-basic', ApiEndpoint::DEVICE_BASICS->value);
        $this->assertEquals('/api/self/sites', ApiEndpoint::SITES->value);
        $this->assertEquals('/v2/api/site/{site}/device', ApiEndpoint::DEVICES_V2->value);
    }
    
    public function testEndpointCases(): void
    {
        $this->assertCount(6, ApiEndpoint::cases());
        $this->assertContains(ApiEndpoint::LOGIN, ApiEndpoint::cases());
        $this->assertContains(ApiEndpoint::LOGOUT, ApiEndpoint::cases());
        $this->assertContains(ApiEndpoint::INFO, ApiEndpoint::cases());
        $this->assertContains(ApiEndpoint::DEVICE_BASICS, ApiEndpoint::cases());
        $this->assertContains(ApiEndpoint::SITES, ApiEndpoint::cases());
        $this->assertContains(ApiEndpoint::DEVICES_V2, ApiEndpoint::cases());
    }
} 
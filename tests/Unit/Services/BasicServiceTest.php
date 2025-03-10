<?php

declare(strict_types=1);

namespace SkyDiablo\UnifiApiClient\Tests\Unit\Services;

use Mockery;
use React\Promise\Promise;
use SkyDiablo\UnifiApiClient\ApiEndpoint;
use SkyDiablo\UnifiApiClient\Services\BasicService;
use SkyDiablo\UnifiApiClient\Tests\TestCase;
use SkyDiablo\UnifiApiClient\UnifiClient;

class BasicServiceTest extends TestCase
{
    private $unifiClientMock;
    private BasicService $basicService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->unifiClientMock = Mockery::mock(UnifiClient::class);
        $this->basicService = new BasicService($this->unifiClientMock);
    }

    public function testGetInfo(): void
    {
        $responseData = [
            'self'   => [
                'admin_id'                     => '6530f2f1ca649c5968c5fa33',
                'email'                        => 'dummy@foo.bar',
                'email_alert_enabled'          => true,
                'email_alert_grouping_delay'   => 60,
                'email_alert_grouping_enabled' => true,
                'html_email_enabled'           => true,
                'is_owner'                     => false,
                'is_professional_installer'    => true,
                'is_super'                     => true,
                'name'                         => 'admin',
                'push_alert_enabled'           => true,
                'requires_new_password'        => false,
                'ui_settings'                  => [
                    'neverCheckForUpdate' => false,
                    'app'                 => 'network',
                    // Weitere UI-Einstellungen gekürzt...
                ],
            ],
            'sites'  => [
                [
                    '_id'            => '6530f107ca649c5968c5fa11',
                    'attr_hidden_id' => 'default',
                    'attr_no_delete' => true,
                    'desc'           => 'Default',
                    'device_count'   => 0,
                    'name'           => 'default',
                    'permissions'    => [],
                    'role'           => 'admin',
                ],
                [
                    '_id'          => '65310c60ca649c5968c5fa3c',
                    'desc'         => 'Production',
                    'device_count' => 13,
                    'is_active'    => true,
                    'name'         => 'erq2fau2',
                    'permissions'  => [],
                    'role'         => 'admin',
                    'timezone'     => 'Europe/Berlin',
                ],
            ],
            'system' => [
                'device_id'                => '349fd5f2-f071-4559-ad88-5effdb7a9e70',
                'has_webrtc_support'       => true,
                'host_meta'                => [
                    'default_image_id'   => '80ff7b60652a1261f8c32e4a868274ce',
                    'id'                 => '94584a8c-d5c9-4f61-8019-c7457e262803',
                    'model_abbreviation' => 'Self Hosted Network Server',
                    'model_fullName'     => 'Self Hosted Network Server',
                    'model_name'         => 'INVALID',
                    'nopadding_image_id' => 'd4dda2af9aed1904a7785d2dbffe8776',
                    'sku'                => 'SELF-HOSTED-NETWORK-SERVER',
                    'topology_image_id'  => 'bd88f2d1f257e7b3dd17b14a8c60c361',
                ],
                'hostname'                 => '127.0.0.1',
                'inform_port'              => 8080,
                'multiple_sites_supported' => true,
                'name'                     => 'Unifi-Controller',
                'standalone'               => [
                    'platform_type' => 'linux',
                ],
                'super_permissions'        => [],
                'uptime'                   => 1463139,
                'version'                  => '9.0.114',
            ],
        ];

        $this->unifiClientMock
            ->shouldReceive('get')
            ->once()
            ->with(ApiEndpoint::INFO)
            ->andReturn(
                new Promise(function ($resolve) use ($responseData) {
                    $resolve($responseData);
                }),
            );

        $promise = $this->basicService->getInfo();

        $promise->then(function ($result) use ($responseData) {
            $this->assertEquals($responseData, $result);
            $this->assertArrayHasKey('self', $result);
            $this->assertArrayHasKey('sites', $result);
            $this->assertArrayHasKey('system', $result);
            $this->assertEquals('admin', $result['self']['name']);
            $this->assertEquals('9.0.114', $result['system']['version']);
            $this->assertCount(2, $result['sites']);
        });
    }
} 
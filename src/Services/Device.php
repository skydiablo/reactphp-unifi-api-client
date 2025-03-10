<?php

declare(strict_types=1);

namespace SkyDiablo\UnifiApiClient\Services;

use React\Promise\PromiseInterface;
use SkyDiablo\UnifiApiClient\ApiEndpoint;

class Device extends BasicService {

    public function getDeviceBasics(string $site = self::DEFAULT_SITE): PromiseInterface
    {
        return $this->unifiClient->get(ApiEndpoint::DEVICE_BASICS, ['site' => $site])
            ->then(fn(array $data) => $data['data'] ?? []);
    }

    public function getDevicesV2(string $site = self::DEFAULT_SITE, bool $separateUnmanaged = false, bool $includeTrafficUsage = false): PromiseInterface
    {
        return $this->unifiClient->get(ApiEndpoint::DEVICES_V2, ['site' => $site], array_filter([
            'includeTrafficUsage' => $includeTrafficUsage,
            'separateUnmanaged' => $separateUnmanaged,
        ]));
    }

}
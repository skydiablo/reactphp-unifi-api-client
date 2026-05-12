<?php

declare(strict_types=1);

namespace SkyDiablo\UnifiApiClient\Services;

use React\Promise\PromiseInterface;
use SkyDiablo\UnifiApiClient\ApiEndpoint;

class Site extends BasicService
{

    public function get(): PromiseInterface
    {
        return $this->unifiClient
            ->get(ApiEndpoint::SITES)
            ->then(fn(array $data) => $data['data'] ?? []);
    }

    public function health(string $site = self::DEFAULT_SITE): PromiseInterface
    {
        return $this->unifiClient
            ->get(ApiEndpoint::SITE_HEALTH, ['site' => $site])
            ->then(fn(array $data) => $data['data'] ?? []);
    }

}
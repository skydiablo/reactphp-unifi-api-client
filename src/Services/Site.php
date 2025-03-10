<?php

declare(strict_types=1);

namespace SkyDiablo\UnifiApiClient\Services;

use React\Promise\PromiseInterface;
use SkyDiablo\UnifiApiClient\ApiEndpoint;

class Site extends BasicService
{

    public function getSites(): PromiseInterface
    {
        return $this->unifiClient
            ->get(ApiEndpoint::SITES)
            ->then(fn(array $data) => $data['data'] ?? []);
    }

}
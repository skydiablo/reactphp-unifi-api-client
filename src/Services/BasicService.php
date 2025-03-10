<?php

declare(strict_types=1);

namespace SkyDiablo\UnifiApiClient\Services;

use React\Promise\PromiseInterface;
use SkyDiablo\UnifiApiClient\ApiEndpoint;
use SkyDiablo\UnifiApiClient\UnifiClient;

class BasicService {

    public const string DEFAULT_SITE = 'default';

    public function __construct(protected UnifiClient $unifiClient)
    {

    }

    public function getInfo(): PromiseInterface
    {
        return $this->unifiClient->get(ApiEndpoint::INFO);
    }

}
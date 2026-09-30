<?php
declare(strict_types=1);

namespace SkyDiablo\UnifiApiClient;

use React\Promise\PromiseInterface;
use React\Socket\ConnectorInterface;
use function React\Promise\resolve;

/**
 * UniFi OS Server / UniFi OS console client.
 *
 * Uses API-key auth and the UniFi OS reverse-proxy prefix while keeping the
 * classic Network Application endpoint paths unchanged.
 */
class UnifiOsClient extends UnifiClient
{
    private const string PATH_PREFIX = 'proxy/network';

    public function __construct(
        string $uri,
        protected string $apiKey,
        ?ConnectorInterface $connector = null
    ) {
        parent::__construct($uri, '', '', $connector);
    }

    protected function buildBaseUri(string $uri): string
    {
        return rtrim($uri, '/') . '/' . self::PATH_PREFIX . '/';
    }

    protected function defaultHeader(array $header = []): array
    {
        return $header + [
            'Content-Type' => 'application/json;charset=utf-8',
            'Accept' => 'application/json, text/plain, */*',
            'Cache-Control' => 'no-cache',
            'X-API-Key' => $this->apiKey,
        ];
    }

    protected function shouldAutoLogin(): bool
    {
        return false;
    }

    public function logout(): PromiseInterface
    {
        return resolve([]);
    }
}

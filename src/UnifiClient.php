<?php
declare(strict_types=1);

namespace SkyDiablo\UnifiApiClient;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use React\Http\Browser;
use React\Http\Message\ResponseException;
use React\Http\Message\Uri;
use React\Promise\PromiseInterface;
use React\Socket\Connector;
use React\Socket\ConnectorInterface;

class UnifiClient
{

    protected Browser $httpClient;

    private ?string $unifiSession = null;
    private ?string $csrfToken = null;

    public function __construct(
        string             $uri,
        protected string   $username,
        protected string   $password,
        ConnectorInterface $connector = null
    )
    {
        $this->httpClient = (new Browser($connector ?? new Connector(
            [
                'tls' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false
                ],
            ]
        )
        ))->withBase(rtrim($uri, '/') . '/');
    }

    protected function defaultHeader(array $header = []): array
    {
        return $header + [
                'Content-Type' => 'application/json;charset=utf-8',
                'Accept' => 'application/json, text/plain, */*',
                'Cache-Control' => 'no-cache',
            ] + ($this->unifiSession ? ['Cookie' => sprintf('unifises=%s; csrf_token=%s', $this->unifiSession, $this->csrfToken)] : [])
            + ($this->csrfToken ? ['X-Csrf-Token' => $this->csrfToken] : []);
    }

    /**
     * @param ApiEndpoint $endpoint
     * @param array $pathParams replace placeholder like "{id}" if given array ['id' => 1]
     * @return Uri
     */
    protected function url(ApiEndpoint $endpoint, array $pathParams = []): UriInterface
    {
        $path = str_replace(
            array_map(function ($key) {
                return sprintf('{%s}', $key);
            }, array_keys($pathParams)),
            $pathParams,
            $endpoint->value
        );
        return new Uri(ltrim($path, '/'));
    }

    protected function body($data): false|string
    {
        return json_encode($data);
    }

    /**
     * @param ResponseInterface $response
     * @return array
     */
    protected function decode(ResponseInterface $response): array
    {
        $ct = $response->getHeader('Content-Type')[0] ?? '';
        if (str_contains($ct, 'application/json')) {
            return json_decode($response->getBody()->getContents(), true);
        }
        return [];
    }

    /**
     * @param array $data
     * @param ApiEndpoint $endpoint
     * @param array $pathParams
     * @param array $queryParams
     * @param bool $autoLogin
     * @return PromiseInterface<array>
     */
    public function post(array $data, ApiEndpoint $endpoint, array $pathParams = [], array $queryParams = [], bool $autoLogin = true): PromiseInterface
    {
        $uri = $this->addQueryParams($queryParams, $this->url($endpoint, $pathParams));
        return $this->httpClient->post(
            $uri,
            $this->defaultHeader(),
            $this->body($data)
        )
            ->then(fn(ResponseInterface $response) => $this->parseSetCookies($response)->decode($response))
            ->catch(function (ResponseException $e) use ($autoLogin, $endpoint, $pathParams, $queryParams, $data) {
                if ($autoLogin) {
                    return $this->login($this->username, $this->password)->then(function () use ($endpoint, $pathParams, $queryParams, $data) {
                        return $this->post($data, $endpoint, $pathParams, $queryParams, false);
                    });
                }
                throw $e;
            });
    }

    /**
     * @param ApiEndpoint $endpoint
     * @param array $pathParams
     * @param array $queryParams
     * @param bool $autoLogin
     * @return PromiseInterface<array>
     */
    public function get(ApiEndpoint $endpoint, array $pathParams = [], array $queryParams = [], bool $autoLogin = true): PromiseInterface
    {
        $uri = $this->addQueryParams($queryParams, $this->url($endpoint, $pathParams));
        return $this->httpClient->get(
            $uri,
            $this->defaultHeader()
        )
            ->then(fn(ResponseInterface $response) => $this->parseSetCookies($response)->decode($response))
            ->catch(function (ResponseException $e) use ($autoLogin, $endpoint, $pathParams, $queryParams) {
                if ($autoLogin) {
                    return $this->login($this->username, $this->password)->then(function () use ($endpoint, $pathParams, $queryParams) {
                        return $this->get($endpoint, $pathParams, $queryParams, false);
                    });
                }
                throw $e;
            });
    }

    protected function addQueryParams(array $queryParams, Uri $uri): UriInterface
    {
        return $uri->withQuery(trim(sprintf('%s&%s', $uri->getQuery(), http_build_query($queryParams)), '&'));
    }

    protected function login(string $username, string $password): PromiseInterface
    {
        $params = [
            'username' => $username,
            'password' => $password,
            'strict' => true,
            'remember' => false,
        ];
        return $this->post($params, ApiEndpoint::LOGIN, autoLogin: false);
    }

    //parse the http header to extract session key and csrf token
    public function parseSetCookies(ResponseInterface $response): self
    {
        $parser = function ($cookie) {
            $result = [];
            foreach (explode(';', $cookie) as $part) {
                if (str_contains($part, '=')) { //key=value
                    [$key, $value] = explode('=', $part, 2);
                    $result[trim($key)] = trim($value);
                } else {
                    $result[trim($part)] = true;
                }
            }
            return $result;
        };
        foreach ($response->getHeader('Set-Cookie') as $header) {
            $cookieParts = $parser($header);
            if (isset($cookieParts['unifises'])) {
                $this->unifiSession = $cookieParts['unifises'];
            }
            if (isset($cookieParts['csrf_token'])) {
                $this->csrfToken = $cookieParts['csrf_token'];
            }
        }
        return $this;
    }

    public function logout(): PromiseInterface
    {
        return $this->post([], ApiEndpoint::LOGOUT)->then(function (array $data) {
            $this->unifiSession = null;
            $this->csrfToken = null;
            return $data;
        });
    }

}
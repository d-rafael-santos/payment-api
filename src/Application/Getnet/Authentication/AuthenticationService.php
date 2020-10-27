<?php

/**
 * Criado por: Rafael Dourado
 * Data: 26/10/2020
 * Hora: 11 : 03
 */

declare(strict_types=1);

namespace Mercatus\PaymentApi\Application\Getnet\Authentication;

use JsonException;
use Mercatus\PaymentApi\Application\Getnet\Exceptions\AuthenticationException;
use Mercatus\PaymentApi\Core\Request\HttpRequestClient;
use Mercatus\PaymentApi\Infrastructure\Remotes\Getnet\GetnetApiConfigInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;

class AuthenticationService implements AuthenticationInterface
{
    protected GetnetApiConfigInterface $apiConfig;
    protected ?CacheItemPoolInterface $cache;

    private ?string $token = null;

    public function __construct(
        GetnetApiConfigInterface $apiConfig,
        ?CacheItemPoolInterface $cache = null
    ) {
        $this->apiConfig = $apiConfig;
        $this->cache = $cache;
    }

    /**
     * @return string
     * @throws AuthenticationException
     * @throws JsonException
     * @throws InvalidArgumentException
     */
    public function authenticate(): string
    {
        if ($this->cache !== null && $this->cache->hasItem($this->apiConfig->getSellerId())) {
            return $this->cache->getItem($this->apiConfig->getSellerId())->get();
        }

        $host = $this->apiConfig->getBaseUri();
        $path = '/auth/oauth/v2/token';
        $headers = [
            'Content-type: application/x-www-form-urlencoded',
            'Authorization: Basic ' . $this->apiConfig->getAuthorizationKey()
        ];

        $options =  'scope=oob&grant_type=client_credentials';

        $response =  HttpRequestClient::post($host . $path, $headers, $options);

        if ($response === false || strpos($response, 'error') !== false) {
            $message = sprintf(
                'Falha ao realizar a autenticação usando a chave de autorização %s',
                $this->apiConfig->getAuthorizationKey()
            );
            throw new AuthenticationException($message);
        }

        $this->cacheAuthToken($response);

        return $response;
    }

    public function isAuthenticated(): bool
    {
        return $this->token !== null;
    }

    public function getAuthenticationToken(): ?string
    {
        return $this->token;
    }

    /**
     * @param string $response
     * @throws InvalidArgumentException
     * @throws JsonException
     */
    private function cacheAuthToken(string $response): void
    {
        $responseArray = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
        $key = $this->apiConfig->getSellerId();
        $expiresAfter = $responseArray['expires_in'] / 2;
        $token = $responseArray['access_token'];

        if ($this->cache !== null) {
            $item = $this->cache->getItem($key);
            $item->set($token);
            $item->expiresAfter($expiresAfter);
            $this->cache->save($item);
        }

        $this->token = $token;
    }
}

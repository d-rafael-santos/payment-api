<?php

/**
 * Criado por: Rafael Dourado
 * Data: 26/10/2020
 * Hora: 11 : 45
 */

declare(strict_types=1);

namespace MercatusTest\PaymentApi\Application\Getnet\Authentication;

use Mercatus\PaymentApi\Application\Getnet\Authentication\AuthenticationService;
use Mercatus\PaymentApi\Application\Getnet\Exceptions\AuthenticationException;
use Mercatus\PaymentApi\Application\Getnet\GetnetEnvironment;
use Mercatus\PaymentApi\Application\SellerInterface;
use Mercatus\PaymentApi\Infrastructure\Remotes\Getnet\GetnetApiConfigInterface;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use function PHPUnit\Framework\assertArrayHasKey;

class AuthenticationServiceTest extends TestCase
{
    protected CacheItemPoolInterface $cache;
    protected AuthenticationService $authService;
    protected GetnetApiConfigInterface $apiConfig;
    protected SellerInterface $seller;
    protected string $authKey;
    protected function setUp(): void
    {
        $this->authKey = $GLOBALS['getnetAuthorizationKey'];
        $this->apiConfig = $this->createMock(GetnetApiConfigInterface::class);
        $this->apiConfig->method('getBaseUri')->willReturn(GetnetEnvironment::SANDBOX_BASE_URI);
        $this->apiConfig->method('getSellerId')->willReturn('2d131e29-8fe3-46d0-945e-d9a4d5488981');
        $this->cache = $this->createMock(CacheItemPoolInterface::class);
        $this->seller = $this->createMock(SellerInterface::class);
    }

    public function testSuccessAuthenticate(): void
    {
        $this->apiConfig->method('getAuthorizationKey')->willReturn($this->authKey);
        $this->authService = new AuthenticationService($this->apiConfig);
        $response = $this->authService->authenticate();

        self::assertJson($response);

        $arr = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

        assertArrayHasKey('access_token', $arr);
    }

    public function testAuthenticationErrorOnFailureAuthenticate(): void
    {
        $invalidKey = 'dGVzdGU6ZW5jb2Rl==';
        $this->apiConfig->method('getAuthorizationKey')->willReturn($invalidKey);
        $this->authService = new AuthenticationService($this->apiConfig);
        $this->expectException(AuthenticationException::class);

        $this->authService->authenticate();
    }

    public function testCacheAuthKeyWhenCacheItemPoolInterfaceIsNotNull(): void
    {
        $this->apiConfig->method('getAuthorizationKey')->willReturn($this->authKey);

        $this->cache->expects(self::once())->method('save')->willReturn(true);

        $this->authService = new AuthenticationService($this->apiConfig, $this->cache);

        $cacheItem = $this->createMock(CacheItemInterface::class);

        $this->cache->method('getItem')->willReturn($cacheItem);

        $this->authService->authenticate();
    }

    public function testRetrieveTokenWhenCached(): void
    {
        $cacheItem = $this->createMock(CacheItemInterface::class);

        $cacheItem->method('get')->willReturn('123');

        $this->cache->expects(self::once())->method('hasItem')->willReturn(true);
        $this->cache->expects(self::once())->method('getItem')->willReturn($cacheItem);
        $this->authService = new AuthenticationService($this->apiConfig, $this->cache);

        $this->cache->expects(self::never())->method('save');

        $this->authService->authenticate();
    }
}

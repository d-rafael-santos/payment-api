<?php

/**
 * Criado por: Rafael Dourado
 * Data: 22/10/2020
 * Hora: 10 : 44
 */

declare(strict_types=1);

namespace MercatusTest\PaymentApi\Infrastructure\Remotes;

use Mercatus\PaymentApi\Infrastructure\Remotes\Getnet\Exceptions\InvalidConfigException;
use Mercatus\PaymentApi\Infrastructure\Remotes\Getnet\GetnetApiConfig;
use Mercatus\PaymentApi\Infrastructure\Remotes\Getnet\GetnetApiConfigInterface;
use PHPUnit\Framework\TestCase;

class GetnetApiConfigTest extends TestCase
{
    protected string $baseUri;
    protected string $authorizationKey;
    protected string $orgId;
    protected string $apiConfig;
    protected string $sellerId;
    protected string $clientId;
    protected string $secretId;
    protected bool $useAntiFraud;

    public function setUp(): void
    {
        $this->baseUri = GetnetApiConfig::SANDBOX_BASE_URI;
        $this->orgId = '';
        $this->sellerId = '2d131e29-8fe3-46d0-945e-d9a4d5488981';
        $this->clientId = '202a037e-a5ce-4a36-9229-b2f1f6925297';
        $this->secretId = 'a9b20dd5-19ee-48c5-9c22-4057038fed7a';
        $this->useAntiFraud = false;
    }

    public function createApiConfig(): GetnetApiConfig
    {
        return new GetnetApiConfig(
            $this->baseUri,
            $this->clientId,
            $this->secretId,
            $this->sellerId,
            $this->orgId,
            $this->useAntiFraud
        );
    }

    public function testValidApiConfig(): void
    {
        $apiConfig = $this->createApiConfig();

        self::assertInstanceOf(GetnetApiConfigInterface::class, $apiConfig);
    }

    public function testInvalidUriException(): void
    {
        $this->baseUri = 'https://fake-homologacao.getnet.com.br';

        $this->expectException(InvalidConfigException::class);

        $this->createApiConfig();
    }

    public function testInvalidOrgIdValue(): void
    {
        $this->orgId = '123';
        $this->useAntiFraud = true;
        $this->expectException(InvalidConfigException::class);
        $this->createApiConfig();
    }

    public function testInvalidOrgIdValueWithWrongBaseUri(): void
    {
        $this->orgId = GetnetApiConfig::PRODUCTION_ORG_ID;
        $this->baseUri = GetnetApiConfig::SANDBOX_BASE_URI;
        $this->useAntiFraud = true;
        $this->expectException(InvalidConfigException::class);
        $this->createApiConfig();
    }

    public function testGetters(): void
    {
        $expectedBase64Key = base64_encode($this->clientId . ':' . $this->secretId);
        $apiConfig = $this->createApiConfig();
        self::assertEquals(GetnetApiConfig::SANDBOX_BASE_URI, $apiConfig->getBaseUri());
        self::assertEquals('', $apiConfig->getOrgId());
        self::assertFalse( $apiConfig->useAntiFraud());
        self::assertEquals($expectedBase64Key, $apiConfig->getAuthorizationKey());
        self::assertEquals($this->sellerId, $apiConfig->getSellerId());
    }

}

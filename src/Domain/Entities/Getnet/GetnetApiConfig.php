<?php

/**
 * Criado por: Rafael Dourado
 * Data: 22/10/2020
 * Hora: 10 : 12
 */

declare(strict_types=1);

namespace Mercatus\PaymentApi\Domain\Entities\Getnet;

use Mercatus\PaymentApi\Domain\Entities\Exceptions\InvalidConfigException;

class GetnetApiConfig implements GetnetApiConfigInterface
{
    public const SANDBOX_BASE_URI = 'https://api-homologacao.getnet.com.br';
    public const PRODUCTION_BASE_URI = 'https://api.getnet.com.br';
    public const SANDBOX_ORG_ID = '1snn5n9w';
    public const PRODUCTION_ORG_ID = 'k8vif92e';
    protected string $baseUri;
    protected string $sellerId;
    protected string $clientId;
    protected string $secretId;
    protected string $orgId;
    protected bool $useAntiFraud;

    /**
     * GetnetApiConfig constructor.
     * @param string $baseUri
     * @param string $clientId
     * @param string $clientSecret
     * @param string $sellerId
     * @param string $orgId
     * @param bool $useAntiFraud
     * @throws InvalidConfigException
     */
    public function __construct(
        string $baseUri,
        string $clientId,
        string $clientSecret,
        string $sellerId,
        string $orgId = '',
        bool $useAntiFraud = false
    ) {
        $this->validateBaseUri($baseUri);
        $this->validateOrgId($orgId, $useAntiFraud, $baseUri);
        $this->baseUri = $baseUri;
        $this->sellerId = $sellerId;
        $this->orgId = $orgId;
        $this->clientId = $clientId;
        $this->secretId = $clientSecret;
        $this->useAntiFraud = $useAntiFraud;
    }

    public function getBaseUri(): string
    {
        return $this->baseUri;
    }

    public function getAuthorizationKey(): string
    {
        $key = $this->clientId . ':' . $this->secretId;

        return base64_encode($key);
    }

    public function getSellerId(): string
    {
        return $this->sellerId;
    }

    public function useAntiFraud(): bool
    {
        return $this->useAntiFraud;
    }

    public function getOrgId(): string
    {
        return $this->orgId;
    }

    /**
     * @param string $uri
     * @throws InvalidConfigException
     */
    private function validateBaseUri(string $uri): void
    {
        if (! in_array($uri, [self::PRODUCTION_BASE_URI, self::SANDBOX_BASE_URI])) {
            throw new InvalidConfigException(
                "$uri não é uma uri válida para essa api. Use " . self::SANDBOX_BASE_URI .
                " em ambiente de homologação ou " . self::PRODUCTION_BASE_URI . " em ambiente de produção"
            );
        }
    }

    /**
     * @param string $orgId
     * @param bool $useAntiFraud
     * @throws InvalidConfigException
     */
    private function validateOrgId(string $orgId, bool $useAntiFraud, $uri): void
    {
        if ($useAntiFraud) {
            if (! in_array($orgId, [self::SANDBOX_ORG_ID, self::PRODUCTION_ORG_ID])) {
                throw new InvalidConfigException("$orgId não é um valor válido quando useAntiFraud = true");
            }

            if (
                ($orgId === self::SANDBOX_ORG_ID && $uri !== self::SANDBOX_BASE_URI)
                || ($orgId === self::PRODUCTION_ORG_ID && $uri !== self::PRODUCTION_BASE_URI)
            ) {
                throw new InvalidConfigException("$orgId não é um valor válido no ambiente $uri");
            }
        }
    }
}

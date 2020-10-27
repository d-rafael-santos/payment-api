<?php

/**
 * Criado por: Rafael Dourado
 * Data: 26/10/2020
 * Hora: 15 : 47
 */

declare(strict_types=1);

namespace MercatusTest\PaymentApi\Application\Getnet\Tokenization;

use Mercatus\PaymentApi\Application\Getnet\Authentication\AuthenticationInterface;
use Mercatus\PaymentApi\Application\Getnet\Authentication\AuthenticationService;
use Mercatus\PaymentApi\Application\Getnet\Exceptions\TokenizationException;
use Mercatus\PaymentApi\Application\Getnet\GetnetEnvironment;
use Mercatus\PaymentApi\Application\Getnet\Tokenization\CardTokenizer;
use Mercatus\PaymentApi\Domain\PaymentMethodInterface;
use Mercatus\PaymentApi\Domain\PaymentMethods\CreditCardInfoInterface;
use Mercatus\PaymentApi\Infrastructure\Remotes\Getnet\GetnetApiConfigInterface;
use Mercatus\PaymentApi\Infrastructure\Remotes\Getnet\PaymentMethodInterface as GetnetPaymentMethod;
use PHPUnit\Framework\TestCase;

class CardTokenizerTest extends TestCase
{
    protected AuthenticationInterface $authService;
    protected PaymentMethodInterface $paymentMethod;
    protected GetnetApiConfigInterface $apiConfig;
    protected CardTokenizer $tokenizer;
    protected string $authKey;
    protected string $sellerId;
    protected function setUp(): void
    {
        $this->authKey = $GLOBALS['getnetAuthorizationKey'];
        $this->sellerId = $GLOBALS['getnetSellerId'];

        $this->apiConfig = $this->createMock(GetnetApiConfigInterface::class);
        $this->apiConfig->method('getBaseUri')->willReturn(GetnetEnvironment::SANDBOX_BASE_URI);
        $this->apiConfig->method('getSellerId')->willReturn($this->sellerId);
        $this->apiConfig->method('getAuthorizationKey')->willReturn($this->authKey);
        $this->apiConfig->method('useAntiFraud')->willReturn(true);
        $this->apiConfig->method('getOrgId')->willReturn(GetnetEnvironment::SANDBOX_ORG_ID);
        $this->authService = new AuthenticationService($this->apiConfig);

    }

    public function testTokenize(): void
    {
        $this->paymentMethod = $this->createMock(GetnetPaymentMethod::class);
        $cardData = $this->createMock(CreditCardInfoInterface::class);
        $cardData->method('getCardNumber')->willReturn('5155901222280001');
        $this->paymentMethod->method('getData')->willReturn($cardData);
        $this->tokenizer = new CardTokenizer($this->authService, $this->apiConfig);

        $token = $this->tokenizer->tokenize($this->paymentMethod);

        self::assertIsString($token);
    }

    public function testExceptionWithInvalidPaymentMethod(): void
    {
        $this->paymentMethod = $this->createMock(GetnetPaymentMethod::class);

        $this->tokenizer = new CardTokenizer($this->authService, $this->apiConfig);

        $this->expectException(TokenizationException::class);

        $this->tokenizer->tokenize($this->paymentMethod);
    }
}

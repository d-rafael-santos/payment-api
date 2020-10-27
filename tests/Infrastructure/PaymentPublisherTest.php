<?php

/**
 * Criado por: Rafael Dourado
 * Data: 22/10/2020
 * Hora: 08 : 37
 */

declare(strict_types=1);

namespace MercatusTest\PaymentApi\Infrastructure;

use Mercatus\PaymentApi\Domain\PaymentApiInterface;
use Mercatus\PaymentApi\Domain\ResponseInterface;
use Mercatus\PaymentApi\Infrastructure\Exceptions\PaymentRequestException;
use Mercatus\PaymentApi\Infrastructure\PaymentPublisher;
use PHPUnit\Framework\TestCase;

class PaymentPublisherTest extends TestCase
{
    protected PaymentApiInterface  $paymentApi;
    protected PaymentPublisher $paymentPublisher;

    /**
     * Testa se a chamada do curl será executada corretamente
     * @throws PaymentRequestException
     */
    public function testSubmitPayment(): void
    {
        $response = $this->paymentPublisher->pay($this->paymentApi);

        self::assertIsString($response);
    }

    public function testThrowsCurlErrorExceptionWithInvalidUrl(): void
    {
        $paymentApi = $this
            ->createStub(PaymentApiInterface::class);
        $paymentApi->method('getBaseUri')->willReturn('http://404.php.net/');
        $paymentApi->method('getPaymentPath')->willReturn('/v1/payments/credit');
        $paymentApi->method('getPostOptions')->willReturn('');
        $paymentApi->method('getHeaders')->willReturn(['Content-Type: application/json']);
        $response = $this->createStub(ResponseInterface::class);
        $paymentApi->method('toResponse')->willReturn($response);

        $this->expectException(PaymentRequestException::class);

        $this->paymentPublisher->pay($paymentApi);
    }

    public function setUp(): void
    {
        $this->paymentPublisher = new PaymentPublisher();
        $this->paymentApi = $this
            ->createStub(PaymentApiInterface::class);
        $this->paymentApi->method('getBaseUri')->willReturn('https://api-homologacao.getnet.com.br');
        $this->paymentApi->method('getPaymentPath')->willReturn('/v1/payments/credit');
        $this->paymentApi->method('getPostOptions')->willReturn('');
        $this->paymentApi->method('getHeaders')->willReturn(['Content-Type: application/json']);
    }
}

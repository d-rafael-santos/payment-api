<?php

/**
 * Criado por: Rafael Dourado
 * Data: 21/10/2020
 * Hora: 14 : 19
 */

declare(strict_types=1);

namespace MercatusTest\PaymentApi\Application;

use Mercatus\PaymentApi\Application\PaymentApiFactoryInterface;
use Mercatus\PaymentApi\Application\PaymentService;
use Mercatus\PaymentApi\Application\PaymentServiceInterface;
use Mercatus\PaymentApi\Application\SellerInterface;
use Mercatus\PaymentApi\Domain\OrderInterface;
use Mercatus\PaymentApi\Domain\PaymentApiInterface;
use Mercatus\PaymentApi\Domain\PaymentMethodInterface;
use Mercatus\PaymentApi\Domain\PaymentHttpRequestHandler;
use Mercatus\PaymentApi\Domain\ResponseInterface;
use PHPUnit\Framework\TestCase;

class PaymentServiceTest extends TestCase
{
    protected PaymentService $paymentService;
    protected PaymentApiFactoryInterface $paymentFactory;
    protected PaymentMethodInterface $paymentMethod;
    protected OrderInterface $order;
    protected SellerInterface $seller;
    protected PaymentHttpRequestHandler $paymentPublisher;

    public function testConstructor(): void
    {
        self::assertInstanceOf(
            PaymentServiceInterface::class,
            $this->paymentService
        );
    }

    /**
     * Testa se a resposta do método pay retorna uma resposta válida
     */
    public function testResponsePay(): void
    {
        $expectedResponse = $this->createStub(ResponseInterface::class);
        $paymentResponse = $this->paymentService->pay($this->paymentMethod, $this->order, $this->seller);

        self::assertEquals($expectedResponse, $paymentResponse);
    }

    /**
     * Deve chamar o método PaymentPublisherInterce.publish
     */
    public function testPublishCall(): void
    {
        $this->paymentPublisher->expects(self::once())
            ->method('pay');

        $this->paymentService->pay($this->paymentMethod, $this->order, $this->seller);
    }

    protected function setUp(): void
    {
        $this->paymentPublisher = $this->createMock(PaymentHttpRequestHandler::class);
        $this->paymentPublisher->method('pay')->willReturn('{"response": "test"}');

        $paymentApi = $this->createStub(PaymentApiInterface::class);
        $paymentApi->method('getBaseUri')->willReturn('https://api-homologacao.getnet.com.br');
        $paymentApi->method('getPaymentPath')->willReturn('/v1/payments/credit');
        $paymentApi->method('getPostOptions')->willReturn('');
        $paymentApi->method('getHeaders')->willReturn(['Content-Type: application/json']);
        $paymentApi->method('toResponse')->willReturn($this->createStub(ResponseInterface::class));

        $this->paymentFactory = $this->createStub(PaymentApiFactoryInterface::class);
        $this->paymentFactory->method('__invoke')->willReturn($paymentApi);

        $this->paymentMethod = $this->createStub(PaymentMethodInterface::class);

        $this->order = $this->createStub(OrderInterface::class);

        $this->seller = $this->createStub(SellerInterface::class);

        $this->paymentService = new PaymentService($this->paymentFactory, $this->paymentPublisher);
    }
}

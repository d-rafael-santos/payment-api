<?php

/**
 * Criado por: Rafael Dourado
 * Data: 28/10/2020
 * Hora: 10 : 16
 */

declare(strict_types=1);

namespace MercatusTest\PaymentApi\Infrastructure\Repositories;

use Mercatus\PaymentApi\Application\PaymentApiFactoryInterface;
use Mercatus\PaymentApi\Application\SellerInterface;
use Mercatus\PaymentApi\Domain\OrderInterface;
use Mercatus\PaymentApi\Domain\PaymentApiInterface;
use Mercatus\PaymentApi\Domain\PaymentMethodInterface;
use Mercatus\PaymentApi\Domain\Repositories\PaymentRepositoryInterface;
use Mercatus\PaymentApi\Domain\ResponseInterface;
use Mercatus\PaymentApi\Infrastructure\Core\Transport\TransportInterface;
use Mercatus\PaymentApi\Infrastructure\Repositories\PaymentRepository;
use PHPUnit\Framework\TestCase;

class PaymentRepositoryTest extends TestCase
{
    protected PaymentRepository $paymentRepository;
    protected PaymentApiFactoryInterface $paymentApiFactory;
    protected PaymentMethodInterface $paymentMethod;
    protected OrderInterface $order;
    protected SellerInterface $seller;
    protected TransportInterface $transport;

    public function testConstructor(): void
    {
        self::assertInstanceOf(
            PaymentRepositoryInterface::class,
            $this->paymentRepository
        );
    }

    /**
     * Testa se a resposta do método pay retorna uma resposta válida
     */
    public function testResponsePay(): void
    {
        $expectedResponse = $this->createStub(ResponseInterface::class);
        $paymentResponse = $this->paymentRepository->pay($this->paymentMethod, $this->order, $this->seller);

        self::assertEquals($expectedResponse, $paymentResponse);
    }

    /**
     * Deve chamar o método PaymentPublisherInterce.publish
     */
    public function testPublishCall(): void
    {
        $this->transport->expects(self::once())
            ->method('post');

        $this->paymentRepository->pay($this->paymentMethod, $this->order, $this->seller);
    }

    protected function setUp(): void
    {
        $this->transport = $this->createMock(TransportInterface::class);
        $this->transport->method('post')->willReturn('{"response": "test"}');

        $paymentApi = $this->createStub(PaymentApiInterface::class);
        $paymentApi->method('getBaseUri')->willReturn('https://api-homologacao.getnet.com.br');
        $paymentApi->method('getPaymentPath')->willReturn('/v1/payments/credit');
        $paymentApi->method('getPostOptions')->willReturn('');
        $paymentApi->method('getHeaders')->willReturn(['Content-Type: application/json']);
        $paymentApi->method('toResponse')->willReturn($this->createStub(ResponseInterface::class));

        $this->paymentApiFactory = $this->createStub(PaymentApiFactoryInterface::class);
        $this->paymentApiFactory->method('__invoke')->willReturn($paymentApi);

        $this->paymentMethod = $this->createStub(PaymentMethodInterface::class);

        $this->order = $this->createStub(OrderInterface::class);

        $this->seller = $this->createStub(SellerInterface::class);

        $this->paymentRepository = new PaymentRepository($this->paymentApiFactory, $this->transport);
    }
}

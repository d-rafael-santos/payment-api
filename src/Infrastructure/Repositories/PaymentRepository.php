<?php

/**
 * Criado por: Rafael Dourado
 * Data: 28/10/2020
 * Hora: 10 : 10
 */

declare(strict_types=1);

namespace Mercatus\PaymentApi\Infrastructure\Repositories;

use Mercatus\PaymentApi\Application\PaymentApiFactoryInterface;
use Mercatus\PaymentApi\Application\SellerInterface;
use Mercatus\PaymentApi\Domain\OrderInterface;
use Mercatus\PaymentApi\Domain\PaymentMethodInterface;
use Mercatus\PaymentApi\Domain\Repositories\PaymentRepositoryInterface;
use Mercatus\PaymentApi\Domain\ResponseInterface;
use Mercatus\PaymentApi\Infrastructure\Core\Transport\TransportInterface;

class PaymentRepository implements PaymentRepositoryInterface
{
    protected PaymentApiFactoryInterface $paymentFactory;
    protected TransportInterface $transport;

    public function __construct(PaymentApiFactoryInterface $paymentFactory, TransportInterface $publisher)
    {
        $this->paymentFactory = $paymentFactory;
        $this->transport = $publisher;
    }

    public function pay(
        PaymentMethodInterface $paymentMethod,
        OrderInterface $order,
        SellerInterface $seller
    ): ResponseInterface {
        $api = ($this->paymentFactory)($paymentMethod, $order, $seller);

        $response = $this->transport->post(
            $api->getBaseUri() . $api->getPaymentPath(),
            $api->getHeaders(),
            $api->getPostOptions()
        );

        return $api->toResponse($response);
    }
}

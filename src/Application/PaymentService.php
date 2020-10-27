<?php

/**
 * Criado por: Rafael Dourado
 * Data: 21/10/2020
 * Hora: 14 : 24
 */

declare(strict_types=1);

namespace Mercatus\PaymentApi\Application;

use Mercatus\PaymentApi\Domain\OrderInterface;
use Mercatus\PaymentApi\Domain\PaymentMethodInterface;
use Mercatus\PaymentApi\Domain\PaymentHttpRequestHandler;
use Mercatus\PaymentApi\Domain\ResponseInterface;

class PaymentService implements PaymentServiceInterface
{
    protected PaymentApiFactoryInterface $paymentFactory;
    protected PaymentHttpRequestHandler $requestHandler;

    public function __construct(PaymentApiFactoryInterface $paymentFactory, PaymentHttpRequestHandler $publisher)
    {
        $this->paymentFactory = $paymentFactory;
        $this->requestHandler = $publisher;
    }

    public function pay(
        PaymentMethodInterface $paymentMethod,
        OrderInterface $order,
        SellerInterface $seller
    ): ResponseInterface {
        $api = ($this->paymentFactory)($paymentMethod, $order, $seller);
        $response = $this->requestHandler->pay($api);
        return $api->toResponse($response);
    }
}

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
use Mercatus\PaymentApi\Domain\ResponseInterface;

interface PaymentServiceInterface
{
    public function pay(
        PaymentMethodInterface $paymentMethod,
        OrderInterface $order,
        SellerInterface $seller
    ): ResponseInterface;
}

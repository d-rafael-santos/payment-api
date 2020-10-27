<?php

/**
 * Criado por: Rafael Dourado
 * Data: 24/10/2020
 * Hora: 09 : 47
 */

declare(strict_types=1);

namespace Mercatus\PaymentApi\Application;

use Mercatus\PaymentApi\Domain\OrderInterface;
use Mercatus\PaymentApi\Domain\PaymentApiInterface;
use Mercatus\PaymentApi\Domain\PaymentMethodInterface;

interface PaymentApiFactoryInterface
{
    public function __invoke(
        PaymentMethodInterface $paymentMethod,
        OrderInterface $order,
        SellerInterface $seller
    ): PaymentApiInterface;
}

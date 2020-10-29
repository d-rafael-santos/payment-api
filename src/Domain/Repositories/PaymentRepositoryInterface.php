<?php

/**
 * Criado por: Rafael Dourado
 * Data: 28/10/2020
 * Hora: 10 : 04
 */

declare(strict_types=1);

namespace Mercatus\PaymentApi\Domain\Repositories;

use Mercatus\PaymentApi\Application\SellerInterface;
use Mercatus\PaymentApi\Domain\OrderInterface;
use Mercatus\PaymentApi\Domain\PaymentMethodInterface;

interface PaymentRepositoryInterface
{
    public function pay(PaymentMethodInterface $paymentMethod, OrderInterface $order, SellerInterface $seller);
}

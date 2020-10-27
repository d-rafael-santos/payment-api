<?php

/**
 * Criado por: Rafael Dourado
 * Data: 26/10/2020
 * Hora: 10 : 18
 */

declare(strict_types=1);

namespace Mercatus\PaymentApi\Application\Getnet\Tokenization;

use Mercatus\PaymentApi\Infrastructure\Remotes\Getnet\PaymentMethodInterface;

interface CardTokenizationInterface
{
    public function tokenize(PaymentMethodInterface $paymentMethod): string;
}

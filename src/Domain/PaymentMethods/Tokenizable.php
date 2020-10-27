<?php

/**
 * Criado por: Rafael Dourado
 * Data: 23/10/2020
 * Hora: 09 : 27
 */

declare(strict_types=1);

namespace Mercatus\PaymentApi\Domain\PaymentMethods;

use Mercatus\PaymentApi\Domain\PaymentMethodInterface;

interface Tokenizable extends PaymentMethodInterface
{
    public function getToken(): ?string;
}

<?php

/**
 * Criado por: Rafael Dourado
 * Data: 21/10/2020
 * Hora: 14 : 28
 */

declare(strict_types=1);

namespace Mercatus\PaymentApi\Domain;

interface OrderInterface
{
    public function getId();

    public function getTotal(): float;
}

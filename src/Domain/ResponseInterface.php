<?php

/**
 * Criado por: Rafael Dourado
 * Data: 21/10/2020
 * Hora: 14 : 29
 */

declare(strict_types=1);

namespace Mercatus\PaymentApi\Domain;

interface ResponseInterface
{
    public function getMessage();
    public function getCode();
    public function getDetails();
}

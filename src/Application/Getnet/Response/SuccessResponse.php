<?php

/**
 * Criado por: Rafael Dourado
 * Data: 27/10/2020
 * Hora: 10 : 32
 */

declare(strict_types=1);

namespace Mercatus\PaymentApi\Application\Getnet\Response;

class SuccessResponse extends AbstractResponse
{
    public function __construct(string $message, string $details)
    {
        parent::__construct(200, $message, $details);
    }
}

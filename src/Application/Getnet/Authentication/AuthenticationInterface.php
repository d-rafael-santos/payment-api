<?php

/**
 * Criado por: Rafael Dourado
 * Data: 24/10/2020
 * Hora: 11 : 26
 */

declare(strict_types=1);

namespace Mercatus\PaymentApi\Application\Getnet\Authentication;

interface AuthenticationInterface
{
    public function authenticate();
    public function isAuthenticated(): bool;
    public function getAuthenticationToken();
}

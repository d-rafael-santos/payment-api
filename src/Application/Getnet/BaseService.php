<?php

/**
 * Criado por: Rafael Dourado
 * Data: 26/10/2020
 * Hora: 10 : 45
 */

declare(strict_types=1);

namespace Mercatus\PaymentApi\Application\Getnet;

use Mercatus\PaymentApi\Application\Getnet\Authentication\AuthenticationInterface;
use Mercatus\PaymentApi\Infrastructure\Remotes\Getnet\GetnetApiConfigInterface;

abstract class BaseService
{
    protected AuthenticationInterface $authService;
    protected GetnetApiConfigInterface $apiConfig;

    public function __construct(AuthenticationInterface $authentication, GetnetApiConfigInterface $apiConfig)
    {
        $this->authService = $authentication;
        $this->apiConfig = $apiConfig;
    }
}

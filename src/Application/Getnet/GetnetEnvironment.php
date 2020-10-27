<?php

/**
 * Criado por: Rafael Dourado
 * Data: 24/10/2020
 * Hora: 11 : 36
 */

declare(strict_types=1);

namespace Mercatus\PaymentApi\Application\Getnet;

class GetnetEnvironment
{
    public const SANDBOX_BASE_URI = 'https://api-homologacao.getnet.com.br';
    public const PRODUCTION_BASE_URI = 'https://api.getnet.com.br';
    public const SANDBOX_ORG_ID = '1snn5n9w';
    public const PRODUCTION_ORG_ID = 'k8vif92e';
}

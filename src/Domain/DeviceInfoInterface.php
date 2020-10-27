<?php

/**
 * Criado por: Rafael Dourado
 * Data: 21/10/2020
 * Hora: 14 : 30
 */

declare(strict_types=1);

namespace Mercatus\PaymentApi\Domain;

interface DeviceInfoInterface
{
    public function getIPAddress();

    public function getId();
}

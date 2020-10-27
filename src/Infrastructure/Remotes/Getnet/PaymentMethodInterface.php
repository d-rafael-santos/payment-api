<?php

/**
 * Criado por: Rafael Dourado
 * Data: 23/10/2020
 * Hora: 14 : 39
 */

declare(strict_types=1);

namespace Mercatus\PaymentApi\Infrastructure\Remotes\Getnet;

use Mercatus\PaymentApi\Domain\DeviceInfoInterface;
use Mercatus\PaymentApi\Domain\PaymentMethodInterface as BasePaymentMethodInterface;
use Mercatus\PaymentApi\Domain\PaymentMethods\Storable;
use Mercatus\PaymentApi\Domain\PaymentMethods\Tokenizable;

interface PaymentMethodInterface extends BasePaymentMethodInterface, Storable, Tokenizable
{
    public function getDeviceInfo(): DeviceInfoInterface;
}

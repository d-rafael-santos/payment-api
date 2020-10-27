<?php

/**
 * Criado por: Rafael Dourado
 * Data: 23/10/2020
 * Hora: 09 : 29
 */

declare(strict_types=1);

namespace Mercatus\PaymentApi\Domain\PaymentMethods;

/**
 * Interface que instâncias de Mercatus\PaymentApi\Domain\PaymentMethodInterface deverão implementar quando seus dados
 * poderão ser salvos.
 * @package Mercatus\PaymentApi\Domain\PaymentMethods
 */
interface Storable
{
    /**
     * Indica se o meio de pagamento deve ser salvo ou não
     * @return bool
     */
    public function mustStore(): bool;
}

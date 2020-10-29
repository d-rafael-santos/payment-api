<?php

/**
 * Criado por: Rafael Dourado
 * Data: 21/10/2020
 * Hora: 14 : 28
 */

declare(strict_types=1);

namespace Mercatus\PaymentApi\Domain;

interface PaymentMethodInterface
{
    /**
     * Retorna as informações do usuário
     * @return UserInterface
     */
    public function getUserInfo(): UserInterface;

    /**
     * Retorna o nome do meio de pagamento
     * @return string
     */
    public function getName(): string;
}

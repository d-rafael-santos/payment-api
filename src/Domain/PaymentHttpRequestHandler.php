<?php

/**
 * Criado por: Rafael Dourado
 * Data: 22/10/2020
 * Hora: 08 : 28
 */

declare(strict_types=1);

namespace Mercatus\PaymentApi\Domain;

/**
 * Interface responsável pela comunicação entre a aplicação e o servidor de pagamento
 * Toda a comunicação externa deverá ser feita por classes que implementem essa interface
 * @package Mercatus\PaymentApi\Domain
 */
interface PaymentHttpRequestHandler
{
    /**
     * Envia a requisição para o servidor
     * @param PaymentApiInterface $paymentApi
     * @return mixed
     */
    public function pay(PaymentApiInterface $paymentApi);
}

<?php

/**
 * Criado por: Rafael Dourado
 * Data: 22/10/2020
 * Hora: 15 : 01
 */

declare(strict_types=1);

namespace Mercatus\PaymentApi\Domain\PaymentMethods;

use Mercatus\PaymentApi\Domain\DataInterface;

/**
 * Interface que define os dados básicos de cartão de crédito que deverão ser usados pela aplicação.
 * @package App\Payment\PaymentMethod\CreditCard
 */
interface CreditCardInfoInterface extends DataInterface
{
    /**
     * @return string número do cartão
     */
    public function getCardNumber(): string;

    /**
     * @return string nome do usuário escrito no cartão
     */
    public function getHolderName(): string;

    /**
     * @return string código de segurança CVV ou CVC
     */
    public function getSecurityCode(): string;

    /**
     * @return string bandeira do cartão (Visa, Mastercard, etc)
     */
    public function getBrand(): string;

    /**
     * @return string mês de Expiração
     */
    public function getExpirationMonth(): string;

    /**
     * @return string ano de Expiração
     */
    public function getExpirationYear(): string;
}

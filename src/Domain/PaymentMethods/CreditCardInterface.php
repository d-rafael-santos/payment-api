<?php

/**
 * Criado por: Rafael Dourado
 * Data: 28/10/2020
 * Hora: 10 : 53
 */

declare(strict_types=1);

namespace Mercatus\PaymentApi\Domain\PaymentMethods;

use Mercatus\PaymentApi\Domain\PaymentMethodInterface;

interface CreditCardInterface extends PaymentMethodInterface
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

    /**
     * @return int número de parcelas
     */
    public function getNumberOfInstallments(): int;

    /**
     * retorna o token do cartão, caso ele tenha sido previamente gerado.
     * @return string|null
     */
    public function getToken(): ?string;
}

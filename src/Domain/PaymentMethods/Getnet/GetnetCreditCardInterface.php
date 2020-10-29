<?php

/**
 * Criado por: Rafael Dourado
 * Data: 29/10/2020
 * Hora: 09 : 16
 */

declare(strict_types=1);

namespace Mercatus\PaymentApi\Domain\PaymentMethods\Getnet;

use Mercatus\PaymentApi\Domain\DeviceInfoInterface;
use Mercatus\PaymentApi\Domain\PaymentMethods\CreditCardInterface;

/**
 * Informações adicionais para pagamento com cartão de crédito usando o autorizador Getnet
 * @package Mercatus\PaymentApi\Domain\PaymentMethods\Getnet
 *
 */
interface GetnetCreditCardInterface extends CreditCardInterface
{
    /**
     * Constantes de tipo de transação sendo:
     * - TRANSACTION_FULL: Pagemento à vista
     * - TRANSACTION_INSTALL_NO_INTEREST: Pagamento parcelado sem juros
     * - TRANSACTION_INSTALL_WITH_INTEREST: Pagamento parcelado com juros;
     */
    public const TRANSACTION_FULL = 'FULL';
    public const TRANSACTION_INSTALL_NO_INTEREST = 'INSTALL_NO_INTEREST';
    public const TRANSACTION_INSTALL_WITH_INTEREST = 'INSTALL_WITH_INTEREST';
    /**
     * Indica se o cartão deverá ser salvo no cofre.
     * @return bool
     */
    public function mustSave(): bool;

    /**
     * Retorna informações do dispositivo. Obrigatório somente quando utilizado sistema de Antifraude da API.
     * @return DeviceInfoInterface|null
     */
    public function getDeviceInfo(): ?DeviceInfoInterface;

    /**
     * Identifica se o crédito será feito com confirmação tardia
     * @return bool
     */
    public function isDelayed(): bool;

    /**
     * Indicativo se transação deve ter o processo de autenticação no emissor, caso isAuthenticated = true,
     * o portador deve ser encaminhado ao site do emissor para autenticação junto ao mesmo.
     * @return bool
     */
    public function isAuthenticated(): bool;

    /**
     * Indicativo se a transação é uma pré autorização de crédito
     * @return bool
     */
    public function isPreAuthorization(): bool;

    /**
     * Tipo de transação. Pagamento completo à vista, parcelado sem juros, parcelado com juros.
     * @return string
     */
    public function getTransactionType(): string;
}

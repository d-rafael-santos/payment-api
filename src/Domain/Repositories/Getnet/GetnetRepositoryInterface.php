<?php

/**
 * Criado por: Rafael Dourado
 * Data: 28/10/2020
 * Hora: 10 : 44
 */

declare(strict_types=1);

namespace Mercatus\PaymentApi\Domain\Repositories\Getnet;

use Mercatus\PaymentApi\Domain\OrderInterface;
use Mercatus\PaymentApi\Domain\PaymentMethods\CreditCardInterface;
use Mercatus\PaymentApi\Domain\PaymentMethods\Getnet\GetnetCreditCardInterface;
use Mercatus\PaymentApi\Domain\ResponseInterface;
use Mercatus\PaymentApi\Domain\UserInterface;
use Mercatus\PaymentApi\Infrastructure\Repositories\Getnet\Exception\AuthenticationException;
use Mercatus\PaymentApi\Infrastructure\Repositories\Getnet\Exception\TokenizationException;

/**
 * Encapsula o acesso à API de pagamento da Getnet nessa interface.
 * Interface GetnetRepositoryInterface
 * @package Mercatus\PaymentApi\Domain\Repositories\Getnet
 */
interface GetnetRepositoryInterface
{
    /**
     * Realiza autenticação na API e retorna o token de acesso gerado.
     * Mais informações em https://developers.getnet.com.br/api#tag/Autenticacao
     * @return string
     * @throws AuthenticationException
     */
    public function authenticate(): string;

    /**
     * Realiza a tokenização do cartão de crédito e rotorna o token gerado pela API.
     * Mais informações em https://developers.getnet.com.br/api#tag/Tokenizacao
     * @param CreditCardInterface $card
     * @return string o token do cartão de crédito
     * @throws TokenizationException|AuthenticationException
     */
    public function tokenizeCard(CreditCardInterface $card): string;

    /**
     * Lista os cartões salvos no cofre pelo usuário.
     * Mais informações em https://developers.getnet.com.br/api#tag/Cofre%2Fpaths%2F~1v1~1cards%2Fget
     * @param UserInterface $user Usuário a quem pertence o cartão salvo.
     * @param string $status Filtro de status dos cartões retornados. Caso não seja informado nenhum filtro, serão
     * retornados os cartões ativos e renovados
     * @return array
     */
    public function getCardsFrom(UserInterface $user, string $status): array;

    /**
     * Realiza um pagamento com cartão de crédito e retorna um @link ResponseInterface.
     * @param GetnetCreditCardInterface $card
     * @param OrderInterface $order
     * @return ResponseInterface
     */
    public function payWithCreditCard(
        GetnetCreditCardInterface $card,
        OrderInterface $order
    ): ResponseInterface;
}

<?php

/**
 * Criado por: Rafael Dourado
 * Data: 20/10/2020
 * Hora: 11 : 52
 */

declare(strict_types=1);

namespace Mercatus\PaymentApi\Domain;

/**
 * Interface PaymentApi
 * Interface responsável pela abstração da API de pagamento. Toda a comunicação entre essa Api e a API do serviço
 * de pagamento escolhida deverá ser feita por meio das classes que implementem essa interface;
 *
 * @package App\Payment\PaymentApi
 */
interface PaymentApiInterface
{
    /**
     * Retorna a uri base da API
     * @return string
     */
    public function getBaseUri(): string;

    /**
     * Retorna o path de pagamento da API
     * @return string
     */
    public function getPaymentPath(): string;

    /**
     * Retorna um array com os headers (chave) e valores (valor) do endpoint
     * @return array
     */
    public function getHeaders(): array;

    /**
     * Retorna os campos POST enviados para o endpoind;
     * @return mixed
     */
    public function getPostOptions();

    /**
     * Converte a resposta do endpoint para array
     * @param $response
     * @return ResponseInterface
     */
    public function toResponse($response): ResponseInterface;
}

<?php

/**
 * Criado por: Rafael Dourado
 * Data: 22/10/2020
 * Hora: 10 : 12
 */

declare(strict_types=1);

namespace Mercatus\PaymentApi\Infrastructure\Remotes\Getnet;

interface GetnetApiConfigInterface
{
    /**
     * @return string Uri base da API. podendo ser https://api.getnet.com.br em ambiente de produção ou
     *  https://api-homologacao.getnet.com.br em ambiente de homologação
     */
    public function getBaseUri(): string;

    /**
     * @return string A chave codificada em base64 que deve ser adicionada ao header Authorization no
     * momento da autenticação. Mais detalhes em https://developers.getnet.com.br/api#tag/Autenticacao
     */
    public function getAuthorizationKey(): string;

    /**
     * @return string seller id do estabelecimento
     */
    public function getSellerId(): string;

    /**
     * @return bool Indica se a Api usará o sistema Antifraude.
     */
    public function useAntiFraud(): bool;

    /**
     * @return string org_id do ambiente. Obrigatório quando useAntifraud() retornar true. Mais detalhes em
     * https://developers.getnet.com.br/api#section/Antifraude/Implementando-o-Device-Fingerprint
     */
    public function getOrgId(): string;
}

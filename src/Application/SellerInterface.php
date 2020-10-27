<?php

/**
 * Criado por: Rafael Dourado
 * Data: 21/10/2020
 * Hora: 14 : 33
 */

declare(strict_types=1);

namespace Mercatus\PaymentApi\Application;

/**
 * Classes que implementem essa interface serão responsáveis apenas por guardar as informações que identifiquem o
 * estabelecimento destino que usará a API. É necessária para que, em serviços de autenticação, seja possível receber
 * as credenciais específicas daquele estabelecimento. Os serviços de autenticação serão responsáveis por manipular
 * os dados do estabelicimento.
 *
 * @package Mercatus\PaymentApi\Application
 */
interface SellerInterface
{
    /**
     * retorna um identificador do estabelecimento
     * @return mixed
     */
    public function getIdentity();
}

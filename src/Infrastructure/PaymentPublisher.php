<?php

/**
 * Criado por: Rafael Dourado
 * Data: 22/10/2020
 * Hora: 08 : 37
 */

declare(strict_types=1);

namespace Mercatus\PaymentApi\Infrastructure;

use Mercatus\PaymentApi\Core\Request\HttpRequestClient;
use Mercatus\PaymentApi\Domain\PaymentApiInterface;
use Mercatus\PaymentApi\Domain\PaymentHttpRequestHandler;
use Mercatus\PaymentApi\Infrastructure\Exceptions\PaymentRequestException;

class PaymentPublisher implements PaymentHttpRequestHandler
{
    /**
     * @param PaymentApiInterface $paymentApi
     * @return string
     * @throws PaymentRequestException
     */
    public function pay(PaymentApiInterface $paymentApi): string
    {
        $url = $paymentApi->getBaseUri() . $paymentApi->getPaymentPath();

        $response = HttpRequestClient::post($url, $paymentApi->getHeaders(), $paymentApi->getPostOptions());

        if ($response === false) {
            throw new PaymentRequestException("'curl_exec' falhou ao retornar uma resposta da URL $url");
        }

        return $response;
    }
}

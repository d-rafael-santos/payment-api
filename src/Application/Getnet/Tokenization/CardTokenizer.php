<?php

/**
 * Criado por: Rafael Dourado
 * Data: 26/10/2020
 * Hora: 15 : 46
 */

declare(strict_types=1);

namespace Mercatus\PaymentApi\Application\Getnet\Tokenization;

use JsonException;
use Mercatus\PaymentApi\Application\Getnet\BaseService;
use Mercatus\PaymentApi\Application\Getnet\Exceptions\TokenizationException;
use Mercatus\PaymentApi\Core\Request\HttpRequestClient;
use Mercatus\PaymentApi\Domain\PaymentMethods\CreditCardInfoInterface;
use Mercatus\PaymentApi\Infrastructure\Remotes\Getnet\PaymentMethodInterface;

class CardTokenizer extends BaseService implements CardTokenizationInterface
{
    /**
     * @param PaymentMethodInterface $paymentMethod
     * @return string
     * @throws TokenizationException
     * @throws JsonException
     */
    public function tokenize(PaymentMethodInterface $paymentMethod): string
    {
        $url = $this->apiConfig->getBaseUri();
        $path = '/v1/tokens/card';
        if (! $this->authService->isAuthenticated()) {
            $this->authService->authenticate();
        }

        $authToken = $this->authService->getAuthenticationToken();

        $cardData = $paymentMethod->getData();

        if (! $cardData instanceof CreditCardInfoInterface) {
            throw new TokenizationException(
                get_class($cardData) . ' não é uma instância de ' . CreditCardInfoInterface::class
            );
        }

        $options['card_number'] = $cardData->getCardNumber();
        $options['customer_id'] = $paymentMethod->getUserInfo()->getId();

        $headers = [
            'Content-type: application/json; charset=utf-8',
            'Authorization: Bearer ' . $authToken,
            'seller_id: ' . $this->apiConfig->getSellerId()
        ];

        $options = json_encode($options, JSON_THROW_ON_ERROR);

        $response = HttpRequestClient::post($url . $path, $headers, $options);

        $response = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

        if (! isset($response['number_token'])) {
            $msg = 'Não foi possível fazer a tokenização do cartão número.' . $cardData->getCardNumber() . '. ';

            if ($response['message']) {
                $msg .= 'Motivo: ' . $response['message']
                    . '. Detalhes: ' . json_encode($response['details'], JSON_THROW_ON_ERROR);
            }

            throw new TokenizationException($msg, $response['status_code']);
        }

        return $response['number_token'];
    }
}

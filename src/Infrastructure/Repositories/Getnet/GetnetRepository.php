<?php

/**
 * Criado por: Rafael Dourado
 * Data: 28/10/2020
 * Hora: 11 : 09
 */

declare(strict_types=1);

namespace Mercatus\PaymentApi\Infrastructure\Repositories\Getnet;

use Mercatus\PaymentApi\Domain\Entities\Getnet\GetnetApiConfigInterface;
use Mercatus\PaymentApi\Domain\OrderInterface;
use Mercatus\PaymentApi\Domain\PaymentMethods\CreditCardInterface;
use Mercatus\PaymentApi\Domain\PaymentMethods\Getnet\GetnetCreditCardInterface;
use Mercatus\PaymentApi\Domain\Repositories\Getnet\GetnetRepositoryInterface;
use Mercatus\PaymentApi\Domain\Response\Getnet\ResponseFactory;
use Mercatus\PaymentApi\Domain\ResponseInterface;
use Mercatus\PaymentApi\Domain\UserInterface;
use Mercatus\PaymentApi\Infrastructure\Core\Transport\TransportInterface;
use Mercatus\PaymentApi\Infrastructure\Repositories\Getnet\Exception\AuthenticationException;
use Mercatus\PaymentApi\Infrastructure\Repositories\Getnet\Exception\TokenizationException;
use Psr\Cache\CacheItemPoolInterface;

class GetnetRepository implements GetnetRepositoryInterface
{
    protected GetnetApiConfigInterface $apiConfig;
    protected TransportInterface $transport;
    protected ?CacheItemPoolInterface $cache;
    public function __construct(
        GetnetApiConfigInterface $apiConfig,
        TransportInterface $transport,
        ?CacheItemPoolInterface $cacheItemPool = null
    ) {
        $this->apiConfig = $apiConfig;
        $this->transport = $transport;
        $this->cache = $cacheItemPool;
    }

    public function authenticate(): string
    {
        if ($this->cache) {
            $cacheItem = $this->cache->getItem($this->apiConfig->getSellerId());
            if ($cacheItem->isHit()) {
                return $cacheItem->get();
            }
        }

        $host = $this->apiConfig->getBaseUri();
        $path = '/auth/oauth/v2/token';
        $headers = [
            'Content-type: application/x-www-form-urlencoded',
            'Authorization: Basic ' . $this->apiConfig->getAuthorizationKey()
        ];

        $options =  'scope=oob&grant_type=client_credentials';
        $jsonResult = $this->transport->post($host . $path, $headers, $options);

        $result = json_decode($jsonResult, true);

        if (array_key_exists('error', $result)) {
            $msg = 'Não foi possível fazer a autenticação da chave %s. Erro: %s. Motivo: %s';

            throw new AuthenticationException(
                sprintf($msg, $this->apiConfig->getAuthorizationKey(), $result['error'], $result['error_description'])
            );
        }

        if (! array_key_exists('access_token', $result)) {
            throw new AuthenticationException('Resposta inesperada do servidor.' . $jsonResult);
        }

        $authToken =  $result['access_token'];

        if ($this->cache) {
            $expires = $result['expires_in'];

            $cacheItem = $this->cache->getItem($this->apiConfig->getSellerId());
            $cacheItem->set($authToken);
            $cacheItem->expiresAfter($expires);

            $this->cache->save($cacheItem);
        }

        return $authToken;
    }

    public function tokenizeCard(CreditCardInterface $card): string
    {
        $cardNumber = $card->getCardNumber();
        $userId = $card->getUserInfo()->getId();

        $authToken = $this->authenticate();

        $host = $this->apiConfig->getBaseUri();
        $path = '/v1/tokens/card';

        $headers = [
            'Content-type: application/json;charset=utf-8',
            'Authorization: Bearer ' . $authToken,
            'seller_id: ' . $this->apiConfig->getSellerId()
        ];

        $options =  [
            'card_number' => $cardNumber,
            'costumer_id' => $userId
        ];

        $options  = json_encode($options);

        $jsonResult = $this->transport->post($host . $path, $headers, $options);
        $result = json_decode($jsonResult, true);

        if (array_key_exists('message', $result)) {
            $msg = 'Não foi possível fazer a tokenização do cartão %s. O servidor retornou a seguinte resposta: %s';
            throw new TokenizationException(sprintf($msg, $cardNumber, $jsonResult));
        }

        return $result['number_token'];
    }

    public function getCardsFrom(UserInterface $user, string $status = 'all'): array
    {
        $host = $this->apiConfig->getBaseUri();
        $path = '/v1/cards';

        $options = [
            'customer_id' => $user->getId(),
            'status' => $status
        ];

        $token = $this->authenticate();

        $headers = [
            'Authorization: Bearer ' . $token,
            'seller_id: ' . $this->apiConfig->getSellerId(),
        ];

        $jsonResponse = $this->transport->get($host . $path, $headers, $options);

        return json_decode($jsonResponse, true, 512, JSON_THROW_ON_ERROR);
    }

    public function payWithCreditCard(
        GetnetCreditCardInterface $card,
        OrderInterface $order
    ): ResponseInterface {
        $authToken = $this->authenticate();

        $url = $this->apiConfig->getBaseUri() . '/v1/payments/credit';
        $headers = [
            'Authorization: Bearer ' . $authToken,
            'Content-type: application/json;charset=utf-8',
        ];

        $postOptions = json_encode($this->createPostOptionsFrom($card, $order));

        $result = $this->transport->post($url, $headers, $postOptions);

        return ResponseFactory::createFromJsonString($result);
    }

    private function createPostOptionsFrom(GetnetCreditCardInterface $card, OrderInterface $order)
    {
        $cardToken = $this->tokenizeCard($card);
        $userInfo = $card->getUserInfo();

        $amount = $order->getTotal() * 100;

        $mustStore = $card->mustSave();

        $userAddress = $userInfo->getAddress();

        $userNameArr = explode(' ', $userInfo->getName());
        $firstName = $userNameArr[0];
        $lastName = $userNameArr[count($userNameArr) - 1];

        $addressArr = [
            'street' => $userAddress->getStreet(),
            'number' => $userAddress->getNumber(),
            'complement' => $userAddress->getComplement(),
            'district' => $userAddress->getDistrict(),
            'city' => $userAddress->getCity(),
            'state' => $userAddress->getState(),
            'country' => $userAddress->getCountry(),
            'postal_code' => $userAddress->getPostalCode()
        ];

        $data = [
            'seller_id' => $this->apiConfig->getSellerId(),
            'amount' => $amount,
            'currency' => 'BRL',
            'order' => [
                'order_id' => $order->getId(),
            ],
            'customer' => [
                'customer_id' => $userInfo->getId(),
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $userInfo->getEmail(),
                'document_type' => $userInfo->getDocumentType(),
                'document_number' => $userInfo->getDocumentNumber(),
                'phone_number' => $userInfo->getPhoneNumber(),
                'billing_address' => $addressArr,
            ],
            'shippings' => [
                [
                    'phone_number' => $userInfo->getPhoneNumber(),
                    'address' => $addressArr,
                ]
            ],
            'credit' => [
                'delayed' => $card->isDelayed(),
                'authenticated' => $card->isAuthenticated(),
                'pre_authorization' => $card->isPreAuthorization(),
                'save_card_data' => $mustStore,
                'transaction_type' => $card->getTransactionType(),
                'number_installments' => $card->getNumberOfInstallments(),
                'card' => [
                    'number_token' => $cardToken,
                    'cardholder_name' => $card->getHolderName(),
                    'security_code' => $card->getSecurityCode(),
                    'expiration_month' => $card->getExpirationMonth(),
                    'expiration_year' => $card->getExpirationYear(),
                ],
            ],
        ];

        if ($this->apiConfig->useAntiFraud() && $card->getDeviceInfo() !== null) {
            $data['device'] = [
                'ip_address' => $card->getDeviceInfo()->getIPAddress(),
                'device_id' => $card->getDeviceInfo()->getId(),
            ];
        }

        return $data;
    }
}

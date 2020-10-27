<?php

/**
 * Criado por: Rafael Dourado
 * Data: 24/10/2020
 * Hora: 10 : 15
 */

declare(strict_types=1);

namespace Mercatus\PaymentApi\Application\Getnet;

use JsonException;
use Mercatus\PaymentApi\Application\Getnet\Authentication\AuthenticationInterface;
use Mercatus\PaymentApi\Application\Getnet\Exceptions\FactoryApiError;
use Mercatus\PaymentApi\Application\Getnet\Tokenization\CardTokenizationInterface;
use Mercatus\PaymentApi\Application\PaymentApiFactoryInterface;
use Mercatus\PaymentApi\Application\SellerInterface;
use Mercatus\PaymentApi\Domain\OrderInterface;
use Mercatus\PaymentApi\Domain\PaymentApiInterface;
use Mercatus\PaymentApi\Domain\PaymentMethodInterface;
use Mercatus\PaymentApi\Domain\PaymentMethods\CreditCardInfoInterface;
use Mercatus\PaymentApi\Infrastructure\Remotes\Getnet\CreditCardPaymentApi;
use Mercatus\PaymentApi\Infrastructure\Remotes\Getnet\GetnetApiConfigInterface;
use Mercatus\PaymentApi\Infrastructure\Remotes\Getnet\PaymentMethodInterface as GetnetPaymentMethodInterface;

class PaymentApiFactory extends BaseService implements PaymentApiFactoryInterface
{
    protected CardTokenizationInterface $tokenizer;
    public function __construct(
        AuthenticationInterface $authService,
        GetnetApiConfigInterface $config,
        CardTokenizationInterface $tokenizer
    ) {
        parent::__construct($authService, $config);
        $this->tokenizer = $tokenizer;
    }

    /**
     * @param PaymentMethodInterface $paymentMethod
     * @param OrderInterface $order
     * @param SellerInterface $seller
     * @return PaymentApiInterface
     * @throws FactoryApiError|JsonException
     */
    public function __invoke(
        PaymentMethodInterface $paymentMethod,
        OrderInterface $order,
        SellerInterface $seller
    ): PaymentApiInterface {
        if (! $paymentMethod instanceof GetnetPaymentMethodInterface) {
            throw new FactoryApiError(
                get_class($paymentMethod) . ' não é uma instância de' . GetnetPaymentMethodInterface::class
            );
        }

        if ($this->authService->isAuthenticated()) {
            $this->authService->authenticate();
        }

        $authKey = $this->authService->getAuthenticationToken();

        $headers = [
            'Content-Type: application/json;charset=utf-8',
            'Authorization: Bearer ' . $authKey,
        ];

        $postOptions = $this->createPostOptionsArrayFrom($paymentMethod, $order, $this->apiConfig);

        return new CreditCardPaymentApi(
            $this->apiConfig->getBaseUri(),
            $headers,
            json_encode($postOptions, JSON_THROW_ON_ERROR)
        );
    }

    /**
     * @param GetnetPaymentMethodInterface $paymentMethod
     * @param OrderInterface $order
     * @param GetnetApiConfigInterface $config
     * @return array
     * @throws FactoryApiError
     */
    private function createPostOptionsArrayFrom(
        GetnetPaymentMethodInterface $paymentMethod,
        OrderInterface $order,
        GetnetApiConfigInterface $config
    ): array {
        if (! $paymentMethod->getData() instanceof CreditCardInfoInterface) {
            throw new FactoryApiError(
                get_class($paymentMethod->getData()) . ' não é uma instância de' . CreditCardInfoInterface::class
            );
        }

        $token = $paymentMethod->getToken();

        $amount = $order->getTotal() * 100;

        $mustStore = $paymentMethod->mustStore();

        $userInfo = $paymentMethod->getUserInfo();

        $userAddress = $userInfo->getAddress();

        /** @var CreditCardInfoInterface $cardData */
        $cardData = $paymentMethod->getData();

        $userNameArr = explode(' ', $userInfo->getName());
        $firstName = $userNameArr[0];
        $lastName = $userNameArr[count($userNameArr) - 1];

        if ($token === null) {
            $token = $this->tokenizer->tokenize($paymentMethod);
        }

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
            'seller_id' => $config->getSellerId(),
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
                'delayed' => false,
                'authenticated' => false,
                'pre_authorization' => false,
                'save_card_data' => $mustStore,
                'transaction_type' => 'FULL',
                'number_installments' => 1,
                'card' => [
                    'number_token' => $token,
                    'cardholder_name' => $cardData->getHolderName(),
                    'security_code' => $cardData->getSecurityCode(),
                    'expiration_month' => $cardData->getExpirationMonth(),
                    'expiration_year' => $cardData->getExpirationYear(),
                ],
            ],
        ];

        if ($this->apiConfig->useAntiFraud()) {
            $data['device'] = [
                'ip_address' => $paymentMethod->getDeviceInfo()->getIPAddress(),
                'device_id' => $paymentMethod->getDeviceInfo()->getId(),
            ];
        }

        return $data;
    }
}

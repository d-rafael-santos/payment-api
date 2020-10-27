<?php

/**
 * Criado por: Rafael Dourado
 * Data: 24/10/2020
 * Hora: 10 : 16
 */

declare(strict_types=1);

namespace MercatusTest\PaymentApi\Application\Getnet;

use JsonException;
use Mercatus\PaymentApi\Application\Getnet\Authentication\AuthenticationInterface;
use Mercatus\PaymentApi\Application\Getnet\Exceptions\FactoryApiError;
use Mercatus\PaymentApi\Application\Getnet\GetnetEnvironment;
use Mercatus\PaymentApi\Application\Getnet\PaymentApiFactory;
use Mercatus\PaymentApi\Application\Getnet\Tokenization\CardTokenizationInterface;
use Mercatus\PaymentApi\Application\PaymentApiFactoryInterface;
use Mercatus\PaymentApi\Application\SellerInterface;
use Mercatus\PaymentApi\Domain\AddressInterface;
use Mercatus\PaymentApi\Domain\DeviceInfoInterface;
use Mercatus\PaymentApi\Domain\OrderInterface;
use Mercatus\PaymentApi\Domain\PaymentMethodInterface;
use Mercatus\PaymentApi\Domain\PaymentMethods\CreditCardInfoInterface;
use Mercatus\PaymentApi\Domain\UserInterface;
use Mercatus\PaymentApi\Infrastructure\Remotes\Getnet\GetnetApiConfigInterface;
use Mercatus\PaymentApi\Infrastructure\Remotes\Getnet\CreditCardPaymentApi;
use Mercatus\PaymentApi\Infrastructure\Remotes\Getnet\PaymentMethodInterface as GetnetPaymentMethodInterfaceAlias;
use MercatusTest\PaymentApi\Fixtures\Fixtures;
use PHPUnit\Framework\TestCase;

class PaymentApiFactoryTest extends TestCase
{
    protected GetnetApiConfigInterface $apiConfig;
    protected UserInterface $user;
    protected CreditCardInfoInterface $cardInfo;
    protected PaymentMethodInterface $paymentMethod;
    protected DeviceInfoInterface $deviceInfo;
    protected OrderInterface $order;
    protected SellerInterface $seller;
    protected CreditCardInfoInterface $creditCardInfo;
    protected PaymentApiFactoryInterface $paymentFactory;
    protected AuthenticationInterface $authService;
    protected CardTokenizationInterface $tokenizer;
    protected string $token;

    protected function setUp(): void
    {
        $this->apiConfig = $this->createMock(GetnetApiConfigInterface::class);
        $authorizationKey = $GLOBALS['getnetAuthorizationKey'];
        $sellerId = $GLOBALS['getnetSellerId'];
        $this->apiConfig->method('getBaseUri')->willReturn(GetnetEnvironment::SANDBOX_BASE_URI);
        $this->apiConfig->method('getAuthorizationKey')->willReturn($authorizationKey);
        $this->apiConfig->method('getSellerId')->willReturn($sellerId);

        $address = $this->createMock(AddressInterface::class);
        $address->method('getStreet')->willReturn('Avenida teste');
        $address->method('getNumber')->willReturn('1444');
        $address->method('getComplement')->willReturn('bloco 0 apto 1');
        $address->method('getDistrict')->willReturn('Bairro teste');
        $address->method('getCity')->willReturn('Mogi das Cruzes');
        $address->method('getState')->willReturn('SP');
        $address->method('getCountry')->willReturn('Brasil');
        $address->method('getPostalCode')->willReturn('12045010');

        $this->user = $this->createMock(UserInterface::class);
        $this->user->method('getId')->willReturn('02a1bdab-796a-40a3-9c96-0d7d34b8a7ee');
        $this->user->method('getName')->willReturn('Rafael Dourado dos Santos');
        $this->user->method('getEmail')->willReturn('rafa_lpark@hotmail.com');
        $this->user->method('getDocumentType')->willReturn('CPF');
        $this->user->method('getDocumentNumber')->willReturn('86088955015');
        $this->user->method('getPhoneNumber')->willReturn('12999999999');
        $this->user->method('getAddress')->willReturn($address);

        $this->deviceInfo = $this->createMock(DeviceInfoInterface::class);
        $this->deviceInfo->method('getIPAddress')->willReturn('127.0.0.1');
        $this->deviceInfo->method('getId')->willReturn('abc_123');

        $this->order = $this->createMock(OrderInterface::class);
        $this->order->method('getId')->willReturn('ee6c69f9-9b2e-44b9-a3a8-80b2709ff191');
        $this->order->method('getTotal')->willReturn(70.27);

        $this->seller = $this->createMock(SellerInterface::class);
        $this->seller->method('getIdentity')->willReturn('1234');

        $this->creditCardInfo = $this->createMock(CreditCardInfoInterface::class);
        $this->creditCardInfo->method('getCardNumber')->willReturn('getCardNumber');
        $this->creditCardInfo->method('getHolderName')->willReturn('Rafael Dourado dos Santos');
        $this->creditCardInfo->method('getSecurityCode')->willReturn('123');
        $this->creditCardInfo->method('getBrand')->willReturn('getBrand');
        $this->creditCardInfo->method('getExpirationMonth')->willReturn('02');
        $this->creditCardInfo->method('getExpirationYear')->willReturn('26');

        $this->paymentMethod = $this->createMock(GetnetPaymentMethodInterfaceAlias::class);
        $this->paymentMethod->method('getUserInfo')->willReturn($this->user);
        $this->paymentMethod->method('getData')->willReturn($this->creditCardInfo);
        $this->paymentMethod->method('getDeviceInfo')->willReturn($this->deviceInfo);

        $this->authService = $this->createMock(AuthenticationInterface::class);

        $this->tokenizer = $this->createMock(CardTokenizationInterface::class);
        $this->token = '73ae4236c3edfaf616cc4e5d98542107c5948bc92c58fc6c2fde00a30c5247e57d643d09882d58879c681de041fea' .
            '6563ec110009a102768dc62cc5e03b00144';

        $this->tokenizer->method('tokenize')->willReturn($this->token);
        $this->paymentFactory = new PaymentApiFactory($this->authService, $this->apiConfig, $this->tokenizer);
    }

    public function testInvokeFactoryWithCreditCardPaymentMethod(): void
    {
        $api = ($this->paymentFactory)($this->paymentMethod, $this->order, $this->seller);

        self::assertInstanceOf(CreditCardPaymentApi::class, $api);
    }

    public function testGettersWithAntiFraudAndCardTokenized(): void
    {
        $this->apiConfig->method('useAntiFraud')->willReturn(true);
        $this->paymentMethod->method('getToken')->willReturn($this->token);

        $tPostOptionsString = Fixtures::getAsString('post.json');

        $this->authService->method('isAuthenticated')->willReturn(true);
        $this->authService->method('getAuthenticationToken')->willReturn('123');

        $api = ($this->paymentFactory)($this->paymentMethod, $this->order, $this->seller);

        $tHeaders = [
            'Content-Type: application/json;charset=utf-8',
            'Authorization: Bearer 123',
        ];

        $tPostOptionsArr = json_decode($tPostOptionsString, true, 512, JSON_THROW_ON_ERROR);
        $postOptionsArr = json_decode($api->getPostOptions(), true, 512, JSON_THROW_ON_ERROR);

        self::assertEquals($tHeaders, $api->getHeaders());
        self::assertEquals($tPostOptionsArr, $postOptionsArr);
        self::assertEquals('https://api-homologacao.getnet.com.br', $api->getBaseUri());
        self::assertEquals('/v1/payments/credit', $api->getPaymentPath());
    }

    /**
     * @throws FactoryApiError|JsonException
     */
    public function testGettersWithAntiFraudAndCardNotTokenized(): void
    {
        $this->apiConfig->method('useAntiFraud')->willReturn(true);

        $tPostOptionsString = Fixtures::getAsString('post.json');

        $this->authService->method('isAuthenticated')->willReturn(true);
        $this->authService->method('getAuthenticationToken')->willReturn('123');
        $tHeaders = [
            'Content-Type: application/json;charset=utf-8',
            'Authorization: Bearer 123',
        ];
        $api = ($this->paymentFactory)($this->paymentMethod, $this->order, $this->seller);

        $tPostOptionsArr = json_decode($tPostOptionsString, true, 512, JSON_THROW_ON_ERROR);
        $postOptionsArr = json_decode($api->getPostOptions(), true, 512, JSON_THROW_ON_ERROR);

        self::assertEquals($tHeaders, $api->getHeaders());
        self::assertEquals($tPostOptionsArr, $postOptionsArr);
        self::assertEquals('https://api-homologacao.getnet.com.br', $api->getBaseUri());
        self::assertEquals('/v1/payments/credit', $api->getPaymentPath());
    }

    public function testMustThrowFactoryApiErrorUsingInvalidPaymentMethod(): void
    {
        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $this->expectException(FactoryApiError::class);

        ($this->paymentFactory)($paymentMethod, $this->order, $this->seller);
    }

    public function testGettersWithoutAntiFraudAndCardTokenized(): void
    {
        $this->apiConfig->method('useAntiFraud')->willReturn(false);

        $tPostOptionsString = Fixtures::getAsString('post.json');

        $this->authService->method('isAuthenticated')->willReturn(true);
        $this->authService->method('getAuthenticationToken')->willReturn('123');
        $tHeaders = [
            'Content-Type: application/json;charset=utf-8',
            'Authorization: Bearer 123',
        ];
        $api = ($this->paymentFactory)($this->paymentMethod, $this->order, $this->seller);

        $tPostOptionsArr = json_decode($tPostOptionsString, true, 512, JSON_THROW_ON_ERROR);
        $postOptionsArr = json_decode($api->getPostOptions(), true, 512, JSON_THROW_ON_ERROR);

        unset($tPostOptionsArr['device']);
        self::assertEquals($tHeaders, $api->getHeaders());
        self::assertEquals($tPostOptionsArr, $postOptionsArr);
        self::assertEquals('https://api-homologacao.getnet.com.br', $api->getBaseUri());
        self::assertEquals('/v1/payments/credit', $api->getPaymentPath());
    }
}

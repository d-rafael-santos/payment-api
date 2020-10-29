<?php

/**
 * Criado por: Rafael Dourado
 * Data: 28/10/2020
 * Hora: 11 : 11
 */

declare(strict_types=1);

namespace MercatusTest\PaymentApi\Infrastructure\Repositories\Getnet;

use Mercatus\PaymentApi\Application\SellerInterface;
use Mercatus\PaymentApi\Domain\AddressInterface;
use Mercatus\PaymentApi\Domain\DeviceInfoInterface;
use Mercatus\PaymentApi\Domain\Entities\Getnet\GetnetApiConfigInterface;
use Mercatus\PaymentApi\Domain\OrderInterface;
use Mercatus\PaymentApi\Domain\PaymentMethods\CreditCardInterface;
use Mercatus\PaymentApi\Domain\PaymentMethods\Getnet\GetnetCreditCardInterface;
use Mercatus\PaymentApi\Domain\Repositories\Getnet\GetnetEnvironment;
use Mercatus\PaymentApi\Domain\Response\Getnet\FailureResponse;
use Mercatus\PaymentApi\Domain\Response\Getnet\SuccessResponse;
use Mercatus\PaymentApi\Domain\UserInterface;
use Mercatus\PaymentApi\Infrastructure\Core\Transport\TransportInterface;
use Mercatus\PaymentApi\Infrastructure\Repositories\Getnet\Exception\AuthenticationException;
use Mercatus\PaymentApi\Infrastructure\Repositories\Getnet\Exception\TokenizationException;
use Mercatus\PaymentApi\Infrastructure\Repositories\Getnet\GetnetRepository;
use MercatusTest\PaymentApi\Fixtures\Fixtures;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

use function PHPUnit\Framework\at;

class GetnetRepositoryTest extends TestCase
{
    protected GetnetRepository $getnetRepository;
    protected GetnetApiConfigInterface $apiConfig;
    protected TransportInterface $transport;
    protected UserInterface $user;
    protected ?DeviceInfoInterface $deviceInfo;
    protected OrderInterface $order;
    protected SellerInterface $seller;
    protected CreditCardInterface $creditCard;
    protected AddressInterface $address;

    protected function setUp(): void
    {
        $authorizationKey = base64_encode('123:abc');
        $sellerId = '60049adf-fea2-41c1-9b5c-bf9aa7a6a666';
        $this->apiConfig = $this->createMock(GetnetApiConfigInterface::class);
        $this->apiConfig->method('getBaseUri')->willReturn(GetnetEnvironment::SANDBOX_BASE_URI);
        $this->apiConfig->method('getAuthorizationKey')->willReturn($authorizationKey);
        $this->apiConfig->method('getSellerId')->willReturn($sellerId);

        $this->transport = $this->createMock(TransportInterface::class);

        $this->address = $this->createMock(AddressInterface::class);
        $this->address->method('getStreet')->willReturn('Avenida teste');
        $this->address->method('getNumber')->willReturn('1444');
        $this->address->method('getComplement')->willReturn('bloco 0 apto 1');
        $this->address->method('getDistrict')->willReturn('Bairro teste');
        $this->address->method('getCity')->willReturn('Mogi das Cruzes');
        $this->address->method('getState')->willReturn('SP');
        $this->address->method('getCountry')->willReturn('Brasil');
        $this->address->method('getPostalCode')->willReturn('12045010');

        $this->user = $this->createMock(UserInterface::class);
        $this->user->method('getId')->willReturn('02a1bdab-796a-40a3-9c96-0d7d34b8a7ee');
        $this->user->method('getName')->willReturn('Rafael Dourado dos Santos');
        $this->user->method('getEmail')->willReturn('rafa_lpark@hotmail.com');
        $this->user->method('getDocumentType')->willReturn('CPF');
        $this->user->method('getDocumentNumber')->willReturn('86088955015');
        $this->user->method('getPhoneNumber')->willReturn('12999999999');
        $this->user->method('getAddress')->willReturn($this->address);

        $this->order = $this->createMock(OrderInterface::class);
        $this->order->method('getId')->willReturn('ee6c69f9-9b2e-44b9-a3a8-80b2709ff191');
        $this->order->method('getTotal')->willReturn(70.27);

        $this->seller = $this->createMock(SellerInterface::class);
        $this->seller->method('getIdentity')->willReturn('1234');

        $this->creditCard = $this->createMock(GetnetCreditCardInterface::class);
        $this->creditCard->method('getCardNumber')->willReturn('getCardNumber');
        $this->creditCard->method('getHolderName')->willReturn('Rafael Dourado dos Santos');
        $this->creditCard->method('getSecurityCode')->willReturn('123');
        $this->creditCard->method('getBrand')->willReturn('getBrand');
        $this->creditCard->method('getExpirationMonth')->willReturn('02');
        $this->creditCard->method('getExpirationYear')->willReturn('26');
        $this->creditCard->method('getUserInfo')->willReturn($this->user);
        $this->creditCard->method('mustSave')->willReturn(true);
        $this->creditCard->method('isDelayed')->willReturn(false);
        $this->creditCard->method('isAuthenticated')->willReturn(false);
        $this->creditCard->method('isPreAuthorization')->willReturn(false);
        $this->creditCard->method('getNumberOfInstallMents')->willReturn(1);
        $this->creditCard->method('getTransactionType')->willReturn(GetnetCreditCardInterface::TRANSACTION_FULL);
    }

    public function testAuthenticate(): void
    {
        $this->getnetRepository = new GetnetRepository($this->apiConfig, $this->transport);

        $postResponse = Fixtures::getAsString('auth-success-response.json');
        $tResponse = '2fcb95a6-fac9-4603-b080-851c5abbf371';

        $host = $this->apiConfig->getBaseUri();
        $path = '/auth/oauth/v2/token';
        $url = $host . $path;

        $headers = [
            'Content-type: application/x-www-form-urlencoded',
            'Authorization: Basic ' . $this->apiConfig->getAuthorizationKey()
        ];

        $options =  'scope=oob&grant_type=client_credentials';

        $this->transport
            ->expects(self::once())
            ->method('post')
            ->with($url, $headers, $options)
            ->willReturn($postResponse);

        $result = $this->getnetRepository->authenticate();

        self::assertEquals($tResponse, $result);
    }

    public function testCacheAuthenticationKeyWhenCachePoolInterfaceImplemented(): void
    {
        $cacheItem = $this->createStub(CacheItemInterface::class);

        $cachePool = $this->createMock(CacheItemPoolInterface::class);

        $cachePool->method('getItem')->willReturn($cacheItem);

        $this->getnetRepository = new GetnetRepository($this->apiConfig, $this->transport, $cachePool);

        $postResponse = Fixtures::getAsString('auth-success-response.json');
        $tResponse = '2fcb95a6-fac9-4603-b080-851c5abbf371';

        $this->transport->expects(self::once())->method('post')->willReturn($postResponse);

        $cachePool->expects(self::once())->method('save');

        $result = $this->getnetRepository->authenticate();

        self::assertEquals($tResponse, $result);
    }

    public function testGetAuthKeyFromCache(): void
    {
        $tResponse = '2fcb95a6-fac9-4603-b080-851c5abbf371';

        $cacheItem = $this->createMock(CacheItemInterface::class);
        $cacheItem->method('isHit')->willReturn(true);
        $cacheItem->expects(self::once())->method('get')->willReturn($tResponse);

        $cachePool = $this->createMock(CacheItemPoolInterface::class);

        $cachePool->method('getItem')->willReturn($cacheItem);

        $this->getnetRepository = new GetnetRepository($this->apiConfig, $this->transport, $cachePool);

        $postResponse = Fixtures::getAsString('auth-success-response.json');

        $this->transport->expects(self::never())->method('post')->willReturn($postResponse);

        $cachePool->expects(self::never())->method('save');

        $result = $this->getnetRepository->authenticate();

        self::assertEquals($tResponse, $result);
    }

    public function testAuthenticationException(): void
    {
        $this->getnetRepository = new GetnetRepository($this->apiConfig, $this->transport);

        $postResponse = Fixtures::getAsString('auth-error-response.json');

        $this->transport->expects(self::once())->method('post')->willReturn($postResponse);

        $this->expectException(AuthenticationException::class);

        $this->getnetRepository->authenticate();
    }

    public function testTokenizeCard(): void
    {
        $this->getnetRepository = new GetnetRepository($this->apiConfig, $this->transport);

        $host = $this->apiConfig->getBaseUri();

        $path = '/v1/tokens/card';
        $url = $host . $path;

        $headers = [
            'Content-type: application/json;charset=utf-8',
            'Authorization: Bearer 2fcb95a6-fac9-4603-b080-851c5abbf371',
            'seller_id: ' . $this->apiConfig->getSellerId()
        ];

        $options =  [
            'card_number' => '1111111111111111',
            'costumer_id' => '123456'
        ];

        $options = json_encode($options);

        $postAuthResponse = Fixtures::getAsString('auth-success-response.json');

        $mockUserInfo = $this->createMock(UserInterface::class);
        $mockUserInfo->method('getId')->willReturn('123456');

        $mockCard = $this->createMock(CreditCardInterface::class);
        $mockCard->method('getCardNumber')->willReturn('1111111111111111');
        $mockCard->method('getUserInfo')->willReturn($mockUserInfo);

        $postTokenResponse = Fixtures::getAsString('card-token-success-response.json');

        $this->transport->expects(at(0))->method('post')->willReturn($postAuthResponse);
        $this->transport
            ->expects(at(1))
            ->method('post')
            ->with($url, $headers, $options)
            ->willReturn($postTokenResponse);

        $result = $this->getnetRepository->tokenizeCard($mockCard);

        $expectedResult = 'dfe05208b105578c070f806c80abd3af09e246827d29b866cf4ce16c2058499'
            . '77c9496cbf0d0234f42339937f327747075f68763537b90b31389e01231d4d13c';
        self::assertEquals($expectedResult, $result);
    }

    public function testTokenizationException(): void
    {
        $this->getnetRepository = new GetnetRepository($this->apiConfig, $this->transport);

        $host = $this->apiConfig->getBaseUri();

        $path = '/v1/tokens/card';
        $url = $host . $path;

        $headers = [
            'Content-type: application/json;charset=utf-8',
            'Authorization: Bearer 2fcb95a6-fac9-4603-b080-851c5abbf371',
            'seller_id: ' . $this->apiConfig->getSellerId()
        ];

        $options =  [
            'card_number' => '1111111111111111',
            'costumer_id' => '123456'
        ];

        $options = json_encode($options);

        $postAuthResponse = Fixtures::getAsString('auth-success-response.json');

        $mockUserInfo = $this->createMock(UserInterface::class);
        $mockUserInfo->method('getId')->willReturn('123456');

        $mockCard = $this->createMock(CreditCardInterface::class);
        $mockCard->method('getCardNumber')->willReturn('1111111111111111');
        $mockCard->method('getUserInfo')->willReturn($mockUserInfo);

        $postTokenResponse = Fixtures::getAsString('card-token-error-response.json');

        $this->transport->expects(at(0))->method('post')->willReturn($postAuthResponse);
        $this->transport
            ->expects(at(1))
            ->method('post')
            ->with($url, $headers, $options)
            ->willReturn($postTokenResponse);

        $this->transport->expects(at(0))->method('post')->willReturn($postAuthResponse);
        $this->transport->expects(at(1))->method('post')->willReturn($postTokenResponse);

        $this->expectException(TokenizationException::class);

        $this->getnetRepository->tokenizeCard($mockCard);
    }

    public function testGetCardsFromUserInterface(): void
    {
        $this->getnetRepository = new GetnetRepository($this->apiConfig, $this->transport);
        $tUserId = '9e5ded62-0ea6-4ebf-9e1d-55046a6e48ae';
        $jsonGetResponse = Fixtures::getAsString('get-card-success-response.json');
        $postAuthResponse = Fixtures::getAsString('auth-success-response.json');
        $mockUser = $this->createMock(UserInterface::class);
        $mockUser->method('getId')->willReturn($tUserId);

        $host = $this->apiConfig->getBaseUri();
        $path = '/v1/cards';

        $options = [
            'customer_id' => $mockUser->getId(),
            'status' => 'all'
        ];

        $headers = [
            'Authorization: Bearer 2fcb95a6-fac9-4603-b080-851c5abbf371',
            'seller_id: ' . $this->apiConfig->getSellerId(),
        ];

        $this->transport
            ->method('get')
            ->with($host . $path, $headers, $options)
            ->willReturn($jsonGetResponse);

        $this->transport->method('post')->willReturn($postAuthResponse);

        $result = $this->getnetRepository->getCardsFrom($mockUser);

        self::assertIsArray($result);
    }

    public function testPayWithCreditCardAntiFraudAndDeviceInfo(): void
    {
        $this->apiConfig->method('useAntiFraud')->willReturn(true);

        $this->getnetRepository = new GetnetRepository($this->apiConfig, $this->transport);

        $this->deviceInfo = $this->createMock(DeviceInfoInterface::class);
        $this->deviceInfo->method('getIPAddress')->willReturn('127.0.0.1');
        $this->deviceInfo->method('getId')->willReturn('abc_123');

        $this->creditCard = $this->createMock(GetnetCreditCardInterface::class);
        $this->creditCard->method('getCardNumber')->willReturn('getCardNumber');
        $this->creditCard->method('getHolderName')->willReturn('Rafael Dourado dos Santos');
        $this->creditCard->method('getSecurityCode')->willReturn('123');
        $this->creditCard->method('getBrand')->willReturn('getBrand');
        $this->creditCard->method('getExpirationMonth')->willReturn('02');
        $this->creditCard->method('getExpirationYear')->willReturn('26');
        $this->creditCard->method('getUserInfo')->willReturn($this->user);
        $this->creditCard->method('mustSave')->willReturn(true);
        $this->creditCard->method('getDeviceInfo')->willReturn($this->deviceInfo);
        $this->creditCard->method('isDelayed')->willReturn(false);
        $this->creditCard->method('isAuthenticated')->willReturn(false);
        $this->creditCard->method('isPreAuthorization')->willReturn(false);
        $this->creditCard->method('getNumberOfInstallMents')->willReturn(1);
        $this->creditCard->method('getTransactionType')->willReturn(GetnetCreditCardInterface::TRANSACTION_FULL);

        // o 1º e 2º post irá executar o curl de autenticação
        $mockAuthResponse = Fixtures::getAsString('auth-success-response.json');
        $this->transport
            ->expects(at(0))
            ->method('post')
            ->willReturn($mockAuthResponse);

        $this->transport
            ->expects(at(1))
            ->method('post')
            ->willReturn($mockAuthResponse);
        // o 3º post irá excutar o curl de tokenização
        $mockTokenResponse = Fixtures::getAsString('card-token-success-response.json');
        $this->transport->
            expects(at(2))
            ->method('post')
            ->willReturn($mockTokenResponse);

        // o 4º post irá executar o curl de pagamento
        $expectedPaymentPostOptions = Fixtures::getAsString('payment-post.json');
        $expectedPaymentPostOptions = json_encode(json_decode($expectedPaymentPostOptions, true));
        $expectedPaymentHeaders = [
            'Authorization: Bearer 2fcb95a6-fac9-4603-b080-851c5abbf371',
            'Content-type: application/json;charset=utf-8',
        ];

        $expectedPaymentUrl = $this->apiConfig->getBaseUri() . '/v1/payments/credit';
        $paymentSuccessResponse = Fixtures::getAsString('payment-success-response.json');
        $this->transport
            ->expects(at(3))
            ->method('post')
            ->with($expectedPaymentUrl, $expectedPaymentHeaders, $expectedPaymentPostOptions)
            ->willReturn($paymentSuccessResponse);

        $result = $this->getnetRepository->payWithCreditCard($this->creditCard, $this->order);

        self::assertInstanceOf(SuccessResponse::class, $result);
    }

    public function testPayWithCreditCardWithoutAntiFraud(): void
    {
        $this->apiConfig->method('useAntiFraud')->willReturn(false);

        $this->getnetRepository = new GetnetRepository($this->apiConfig, $this->transport);

        $this->creditCard->method('getDeviceInfo')->willReturn(null);

        // o 1º e 2º post irá executar o curl de autenticação
        $mockAuthResponse = Fixtures::getAsString('auth-success-response.json');
        $this->transport
            ->expects(at(0))
            ->method('post')
            ->willReturn($mockAuthResponse);

        $this->transport
            ->expects(at(1))
            ->method('post')
            ->willReturn($mockAuthResponse);
        // o 3º post irá excutar o curl de tokenização
        $mockTokenResponse = Fixtures::getAsString('card-token-success-response.json');
        $this->transport->
        expects(at(2))
            ->method('post')
            ->willReturn($mockTokenResponse);

        // o 4º post irá executar o curl de pagamento
        $expectedPaymentPostOptions = Fixtures::getAsString('payment-post.json');
        $expectedPaymentPostOptions = json_decode($expectedPaymentPostOptions, true);

        unset($expectedPaymentPostOptions['device']);

        $expectedPaymentPostOptions = json_encode($expectedPaymentPostOptions);
        $expectedPaymentHeaders = [
            'Authorization: Bearer 2fcb95a6-fac9-4603-b080-851c5abbf371',
            'Content-type: application/json;charset=utf-8',
        ];

        $expectedPaymentUrl = $this->apiConfig->getBaseUri() . '/v1/payments/credit';
        $paymentSuccessResponse = Fixtures::getAsString('payment-success-response.json');
        $this->transport
            ->expects(at(3))
            ->method('post')
            ->with($expectedPaymentUrl, $expectedPaymentHeaders, $expectedPaymentPostOptions)
            ->willReturn($paymentSuccessResponse);

        $result = $this->getnetRepository->payWithCreditCard($this->creditCard, $this->order);

        self::assertInstanceOf(SuccessResponse::class, $result);
    }

    public function testPaymentFailureResponse(): void
    {
        $this->apiConfig->method('useAntiFraud')->willReturn(false);

        $this->getnetRepository = new GetnetRepository($this->apiConfig, $this->transport);

        $this->creditCard->method('getDeviceInfo')->willReturn(null);

        // o 1º e 2º post irá executar o curl de autenticação
        $mockAuthResponse = Fixtures::getAsString('auth-success-response.json');
        $this->transport
            ->expects(at(0))
            ->method('post')
            ->willReturn($mockAuthResponse);

        $this->transport
            ->expects(at(1))
            ->method('post')
            ->willReturn($mockAuthResponse);
        // o 3º post irá executar o curl de tokenização
        $mockTokenResponse = Fixtures::getAsString('card-token-success-response.json');
        $this->transport->
        expects(at(2))
            ->method('post')
            ->willReturn($mockTokenResponse);

        // o 4º post irá executar o curl de pagamento
        $expectedPaymentPostOptions = Fixtures::getAsString('payment-post.json');
        $expectedPaymentPostOptions = json_decode($expectedPaymentPostOptions, true);

        unset($expectedPaymentPostOptions['device']);

        $expectedPaymentPostOptions = json_encode($expectedPaymentPostOptions);
        $expectedPaymentHeaders = [
            'Authorization: Bearer 2fcb95a6-fac9-4603-b080-851c5abbf371',
            'Content-type: application/json;charset=utf-8',
        ];

        $expectedPaymentUrl = $this->apiConfig->getBaseUri() . '/v1/payments/credit';
        $paymentFailResponse = Fixtures::getAsString('payment-failure-response.json');
        $this->transport
            ->expects(at(3))
            ->method('post')
            ->with($expectedPaymentUrl, $expectedPaymentHeaders, $expectedPaymentPostOptions)
            ->willReturn($paymentFailResponse);

        $result = $this->getnetRepository->payWithCreditCard($this->creditCard, $this->order);

        self::assertInstanceOf(FailureResponse::class, $result);
    }
}

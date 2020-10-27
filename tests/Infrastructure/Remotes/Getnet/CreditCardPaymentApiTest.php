<?php

/**
 * Criado por: Rafael Dourado
 * Data: 22/10/2020
 * Hora: 10 : 08
 */

declare(strict_types=1);

namespace MercatusTest\PaymentApi\Infrastructure\Remotes\Getnet;

use Mercatus\PaymentApi\Domain\PaymentApiInterface;
use Mercatus\PaymentApi\Infrastructure\Remotes\Getnet\CreditCardPaymentApi;
use PHPUnit\Framework\TestCase;

class CreditCardPaymentApiTest extends TestCase
{
    public function testConstructor()
    {
        $headers = [
            'Content-Type: application/json; charset=utf-8',
            'Authorization: Bearer 123',
        ];
        $tPost  = '{"option": "test"}';

        $api = new CreditCardPaymentApi(
            'https://api-homologacao.getnet.com.br',
            $headers,
            $tPost,
        );
        self::assertInstanceOf(PaymentApiInterface::class, $api);
        self::assertEquals('https://api-homologacao.getnet.com.br', $api->getBaseUri());
        self::assertEquals('/v1/payments/credit', $api->getPaymentPath());
        self::assertEquals($headers, $api->getHeaders());
        self::assertJson($api->getPostOptions());
    }
}

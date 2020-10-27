<?php

/**
 * Criado por: Rafael Dourado
 * Data: 26/10/2020
 * Hora: 13 : 38
 */

declare(strict_types=1);

namespace MercatusTest\PaymentApi\Core\Request;

use Mercatus\PaymentApi\Core\Request\HttpRequestClient;
use PHPUnit\Framework\TestCase;

class HttpRequestTest extends TestCase
{
    public function testGet()
    {
        $url = 'https://postman-echo.com/get?foo1=bar1&foo2=bar2';
        $response = json_decode(HttpRequestClient::get($url, [], ''), true, 512, JSON_THROW_ON_ERROR);

        self::assertArrayHasKey('args', $response);
        self::assertArrayHasKey('url', $response);
    }

    /**
     * @throws \JsonException
     */
    public function testPost()
    {
        $host = 'https://postman-echo.com/post';

        $headers = [
            'Content-type: application/x-www-form-urlencoded',
        ];

        $options = ['foo1=bar1&foo2=bar2'];

        $response = json_decode(
            HttpRequestClient::post($host, $headers, $options),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        self::assertArrayHasKey('args', $response);
        self::assertArrayHasKey('url', $response);
    }

}

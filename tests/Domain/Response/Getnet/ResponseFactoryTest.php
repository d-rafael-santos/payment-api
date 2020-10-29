<?php

/**
 * Criado por: Rafael Dourado
 * Data: 29/10/2020
 * Hora: 10 : 18
 */

declare(strict_types=1);

namespace MercatusTest\PaymentApi\Domain\Response\Getnet;

use Mercatus\PaymentApi\Domain\Response\Getnet\Exceptions\UnexpectedResponseException;
use Mercatus\PaymentApi\Domain\Response\Getnet\FailureResponse;
use Mercatus\PaymentApi\Domain\Response\Getnet\ResponseFactory;
use Mercatus\PaymentApi\Domain\Response\Getnet\SuccessResponse;
use MercatusTest\PaymentApi\Fixtures\Fixtures;
use PHPUnit\Framework\TestCase;

class ResponseFactoryTest extends TestCase
{
    protected ResponseFactory $responseFactory;
    protected string $jsonSuccess;
    protected string $jsonFailure;

    protected function setUp(): void
    {
        $this->jsonFailure = Fixtures::getAsString('payment-failure-response.json');
        $this->jsonSuccess = Fixtures::getAsString('payment-success-response.json');
    }

    public function testSuccessResponseWithSuccessJson(): void
    {
        $response = ResponseFactory::createFromJsonString($this->jsonSuccess);

        self::assertInstanceOf(SuccessResponse::class, $response);
    }

    public function testFailureResponseWithFailureJson(): void
    {
        $response = ResponseFactory::createFromJsonString($this->jsonFailure);

        self::assertInstanceOf(FailureResponse::class, $response);
    }

    public function testUnexpectedResponseExceptionWithInvalidJson(): void
    {
        $this->expectException(UnexpectedResponseException::class);
        ResponseFactory::createFromJsonString('not a json');
    }

    public function testUnexpectedResponseExceptionWithWrongJson(): void
    {
        $this->expectException(UnexpectedResponseException::class);

        ResponseFactory::createFromJsonString('{"test1": "value1"}');
    }
}

<?php

/**
 * Criado por: Rafael Dourado
 * Data: 22/10/2020
 * Hora: 09 : 54
 */

declare(strict_types=1);

namespace Mercatus\PaymentApi\Infrastructure\Remotes\Getnet;

use Mercatus\PaymentApi\Application\Getnet\Exceptions\UnexpectedResponseException;
use Mercatus\PaymentApi\Application\Getnet\Response\ResponseFactory;
use Mercatus\PaymentApi\Domain\PaymentApiInterface;
use Mercatus\PaymentApi\Domain\ResponseInterface;

class CreditCardPaymentApi implements PaymentApiInterface
{
    protected string $paymentPath;
    protected string $baseUri;
    protected string $postOptions;
    protected array $headers;

    public function __construct(
        string $baseUri,
        array $headers,
        string $postOptions,
        string $paymentPath  = '/v1/payments/credit'
    ) {
        $this->paymentPath = $paymentPath;
        $this->baseUri = $baseUri;
        $this->postOptions = $postOptions;
        $this->headers = $headers;
    }

    public function getBaseUri(): string
    {
        return $this->baseUri;
    }

    public function getPaymentPath(): string
    {
        return $this->paymentPath;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getPostOptions()
    {
        return $this->postOptions;
    }

    /**
     * @param $response
     * @return ResponseInterface
     * @throws UnexpectedResponseException
     */
    public function toResponse($response): ResponseInterface
    {
        return ResponseFactory::createFromJsonString($response);
    }
}

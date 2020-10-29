<?php

/**
 * Criado por: Rafael Dourado
 * Data: 27/10/2020
 * Hora: 10 : 35
 */

declare(strict_types=1);

namespace Mercatus\PaymentApi\Domain\Response\Getnet;

use JsonException;
use Mercatus\PaymentApi\Domain\Response\Getnet\Exceptions\UnexpectedResponseException;
use Mercatus\PaymentApi\Domain\ResponseInterface;

class ResponseFactory
{
    /**
     * @param string $json
     * @return ResponseInterface
     * @throws UnexpectedResponseException
     */
    public static function createFromJsonString(string $json): ResponseInterface
    {
        try {
            $array = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new UnexpectedResponseException("$json não é um json válido", 400);
        }

        if (isset($array['status']) && $array['status'] === 'APPROVED') {
            $message = 'Aprovado';
            return new SuccessResponse($message, $json);
        }

        if (isset($array['message'])) {
            return new FailureResponse((int) $array['status_code'], $array['message'], $json);
        }

        throw new UnexpectedResponseException('Não foi possível criar um ResponseInterface com o json' . $json);
    }
}

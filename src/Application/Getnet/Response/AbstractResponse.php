<?php

/**
 * Criado por: Rafael Dourado
 * Data: 27/10/2020
 * Hora: 10 : 33
 */

declare(strict_types=1);

namespace Mercatus\PaymentApi\Application\Getnet\Response;

use Mercatus\PaymentApi\Domain\ResponseInterface;

class AbstractResponse implements ResponseInterface
{
    protected int $code;

    protected string $message;

    protected string $details;

    /**
     * AbstractResponse constructor.
     * @param int $code
     * @param string $message
     * @param string $details
     */
    public function __construct(int $code, string $message, string $details)
    {
        $this->code = $code;
        $this->message = $message;
        $this->details = $details;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getDetails()
    {
        return $this->details;
    }
}

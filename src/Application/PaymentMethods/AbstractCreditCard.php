<?php

/**
 * Criado por: Rafael Dourado
 * Data: 22/10/2020
 * Hora: 15 : 09
 */

declare(strict_types=1);

namespace Mercatus\PaymentApi\Application\PaymentMethods;

use Mercatus\PaymentApi\Domain\PaymentMethodInterface;
use Mercatus\PaymentApi\Domain\PaymentMethods\CreditCardInfoInterface;
use Mercatus\PaymentApi\Domain\UserInterface;

abstract class AbstractCreditCard implements PaymentMethodInterface
{
    protected UserInterface $user;
    protected CreditCardInfoInterface $data;

    public function __construct(UserInterface $user, CreditCardInfoInterface $cardData)
    {
        $this->user = $user;
        $this->data = $cardData;
    }

    public function getUserInfo(): UserInterface
    {
        return $this->user;
    }

    public function getData(): CreditCardInfoInterface
    {
        return $this->data;
    }

    public function getName(): string
    {
        return 'credit_card';
    }
}

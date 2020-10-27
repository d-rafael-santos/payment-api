<?php

/**
 * Criado por: Rafael Dourado
 * Data: 23/10/2020
 * Hora: 14 : 40
 */

declare(strict_types=1);

namespace Mercatus\PaymentApi\Infrastructure\Remotes\Getnet;

use Mercatus\PaymentApi\Application\PaymentMethods\AbstractCreditCard;
use Mercatus\PaymentApi\Domain\DeviceInfoInterface;
use Mercatus\PaymentApi\Domain\PaymentMethods\CreditCardInfoInterface;
use Mercatus\PaymentApi\Domain\UserInterface;

class CreditCard extends AbstractCreditCard implements PaymentMethodInterface
{
    protected bool $mustStore;
    protected ?string $token;
    protected ?DeviceInfoInterface $deviceInfo;

    public function __construct(
        UserInterface $user,
        CreditCardInfoInterface $cardData,
        bool $mustStore,
        ?string $token,
        ?DeviceInfoInterface $deviceInfo = null
    ) {
        $this->mustStore = $mustStore;
        $this->token = $token;
        $this->deviceInfo = $deviceInfo;
        parent::__construct($user, $cardData);
    }

    public function getData(): CreditCardInfoInterface
    {
        return parent::getData();
    }

    public function mustStore(): bool
    {
        return $this->mustStore;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function getDeviceInfo(): DeviceInfoInterface
    {
        return $this->deviceInfo;
    }
}

<?php

/**
 * Criado por: Rafael Dourado
 * Data: 20/10/2020
 * Hora: 11 : 53
 */

declare(strict_types=1);

namespace Mercatus\PaymentApi\Domain;

interface UserInterface
{
    public function getId();

    public function getName();

    public function getEmail();

    public function getDocumentType();

    public function getDocumentNumber();

    public function getAddress(): AddressInterface;

    public function getPhoneNumber();
}

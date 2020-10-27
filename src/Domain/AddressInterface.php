<?php

/**
 * Criado por: Rafael Dourado
 * Data: 20/10/2020
 * Hora: 11 : 55
 */

declare(strict_types=1);

namespace Mercatus\PaymentApi\Domain;

interface AddressInterface
{
    public function getStreet();

    public function getNumber();

    public function getComplement();

    public function getDistrict();

    public function getCity();

    public function getState();

    public function getCountry();

    public function getPostalCode();
}

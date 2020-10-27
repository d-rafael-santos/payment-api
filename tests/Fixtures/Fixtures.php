<?php

/**
 * Criado por: Rafael Dourado
 * Data: 26/10/2020
 * Hora: 09 : 36
 */

declare(strict_types=1);

namespace MercatusTest\PaymentApi\Fixtures;

class Fixtures
{
    public static function getAsString(string $fileName) {
        return file_get_contents('./tests/Fixtures/' . $fileName);
    }
}

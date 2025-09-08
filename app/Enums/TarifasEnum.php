<?php

namespace App\Enums;

use OpenApi\Attributes as OA;

#[OA\Schema(type: 'string')]
enum TarifasEnum: string
{
    case COMPROBANTE = 'comprobante';
    case ESTABLECIMIENTO = 'establecimiento';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
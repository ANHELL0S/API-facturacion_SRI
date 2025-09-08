<?php

namespace App\Enums;

use OpenApi\Attributes as OA;

#[OA\Schema(type: 'string')]
enum AmbientesEnum: string
{
    case PRUEBAS = '1';
    case PRODUCCION = '2';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
<?php

namespace App\Enums;

use OpenApi\Attributes as OA;

#[OA\Schema(type: 'string')]
enum CodigosImpuestosEnum: int
{
    case IVA = 2;
    case ICE = 3;
    case IRBPNR = 5;

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function labels(): array
    {
        return [
            self::IVA => 'IVA',
            self::ICE => 'ICE',
            self::IRBPNR => 'IRBPNR',
        ];
    }

    public static function getLabel(string $value): ?string
    {
        return self::labels()[$value] ?? null;
    }
}
<?php

namespace App\Enums;

use OpenApi\Attributes as OA;

#[OA\Schema(type: 'string')]
enum TipoIdentificacionEnum: string
{
    case RUC = '04';
    case CEDULA = '05';
    case PASAPORTE = '06';
    case VENTA_A_CONSUMIDOR_FINAL = '07';
    case IDENTIFICACION_DEL_EXTERIOR = '08';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function labels(): array
    {
        return [
            self::RUC => 'RUC',
            self::CEDULA => 'Cédula',
            self::PASAPORTE => 'Pasaporte',
            self::VENTA_A_CONSUMIDOR_FINAL => 'Venta a Consumidor Final',
            self::IDENTIFICACION_DEL_EXTERIOR => 'Identificación del Exterior',
        ];
    }

    public static function getLabel(string $value): ?string
    {
        return self::labels()[$value] ?? null;
    }
}
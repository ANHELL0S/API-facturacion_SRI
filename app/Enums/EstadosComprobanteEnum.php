<?php

namespace App\Enums;

enum EstadosComprobanteEnum: string
{
    case PENDIENTE = 'pendiente';
    case PROCESANDO = 'procesando';
    case FIRMADO = 'firmado';
    case AUTORIZADO = 'autorizado';
    case RECHAZADO = 'rechazado';
    case FALLIDO = 'fallido';
    case REINTENTANDO = 'reintentando';
    case NECESITA_CORRECCION = 'necesita_correccion';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

}

<?php

namespace App\Enums;

enum TipoComprobanteEnum: string
{

    case FACTURA = '01'; //FACTURA

    case LIQUIDACION_CBPS = '03'; //LIQUIDACIÓN DE COMPRA DE BIENES Y PRESTACIÓN DE SERVICIOS

    case NOTA_CREDITO = '04'; //NOTA DE CRÉDITO

    case NOTA_DEBITO = '05'; //NOTA DE DÉBITO

    case GUIA_REMISION = '06'; //GUÍA DE REMISIÓN

    case COMPROBANTE_RETENCION = '07'; //COMPROBANTE DE RETENCIÓN


    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

}

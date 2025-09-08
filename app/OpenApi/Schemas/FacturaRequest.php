<?php

namespace App\OpenApi\Schemas;

use App\Enums\TipoIdentificacionEnum;
use OpenApi\Attributes as OA;


#[OA\Schema(
    schema: 'FacturaRequest',
    title: 'FacturaRequest',
    description: 'Request para crear una factura',
    type: 'object',
    required: [
        'fechaEmision', 'tipoIdentificacionComprador', 'razonSocialComprador',
        'identificacionComprador', 'totalSinImpuestos', 'totalDescuento',
        'totalConImpuestos', 'importeTotal', 'pagos', 'detalles'
    ],
    properties: [
        new OA\Property(property: 'fechaEmision', type: 'string', format: 'date', example: '2025-04-29'),
        new OA\Property(property: 'tipoIdentificacionComprador', type: 'string', maxLength: 2, enum: ['04', '05', '06', '07', '08'], example: TipoIdentificacionEnum::RUC->value),
        new OA\Property(property: 'razonSocialComprador', type: 'string', maxLength: 300),
        new OA\Property(property: 'identificacionComprador', type: 'string', maxLength: 20),
        new OA\Property(property: 'direccionComprador', type: 'string', maxLength: 300, nullable: true),

        new OA\Property(property: 'totalSinImpuestos', type: 'number', format: 'float'),
        new OA\Property(property: 'totalDescuento', type: 'number', format: 'float'),

        new OA\Property(
            property: 'totalConImpuestos',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/TotalImpuesto')
        ),

        new OA\Property(property: 'importeTotal', type: 'number', format: 'float'),

        new OA\Property(
            property: 'pagos',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/Pago')
        ),

        new OA\Property(
            property: 'detalles',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/DetalleFactura')
        ),

        new OA\Property(
            property: 'infoAdicional',
            type: 'array',
            items: new OA\Items(type: 'string'),
            example: [
                'email' => 'Kt4hj@example.com',
                'telefono' => '0999999999',
            ]
        )
    ]
)]
class FacturaRequest {}


#[OA\Schema(
    schema: 'TotalImpuesto',
    type: 'object',
    required: ['codigo', 'codigoPorcentaje', 'baseImponible', 'valor'],
    properties: [
        new OA\Property(property: 'codigo', type: 'integer', enum: [2, 3, 5]),
        new OA\Property(property: 'codigoPorcentaje', type: 'integer', minimum: 0, maximum: 9999),
        new OA\Property(property: 'baseImponible', type: 'number', format: 'float'),
        new OA\Property(property: 'valor', type: 'number', format: 'float')
    ]
)]
class TotalImpuesto {}

#[OA\Schema(
    schema: 'Pago',
    type: 'object',
    required: ['formaPago', 'total'],
    properties: [
        new OA\Property(property: 'formaPago', type: 'string', maxLength: 2),
        new OA\Property(property: 'total', type: 'number', format: 'float')
    ]
)]
class Pago {}

#[OA\Schema(
    schema: 'DetalleFactura',
    type: 'object',
    required: ['codigoPrincipal', 'descripcion', 'cantidad', 'precioUnitario', 'descuento', 'precioTotalSinImpuesto', 'impuestos'],
    properties: [
        new OA\Property(property: 'codigoPrincipal', type: 'string', maxLength: 25),
        new OA\Property(property: 'codigoAuxiliar', type: 'string', maxLength: 25, nullable: true),
        new OA\Property(property: 'descripcion', type: 'string', maxLength: 300),
        new OA\Property(property: 'cantidad', type: 'number', format: 'float'),
        new OA\Property(property: 'precioUnitario', type: 'number', format: 'float'),
        new OA\Property(property: 'descuento', type: 'number', format: 'float'),
        new OA\Property(property: 'precioTotalSinImpuesto', type: 'number', format: 'float'),
        new OA\Property(
            property: 'impuestos',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/Impuesto')
        )
    ]
)]
class DetalleFactura {}

#[OA\Schema(
    schema: 'Impuesto',
    type: 'object',
    required: ['codigo', 'codigoPorcentaje', 'tarifa', 'baseImponible', 'valor'],
    properties: [
        new OA\Property(property: 'codigo', type: 'integer', enum: [2, 3, 5]),
        new OA\Property(property: 'codigoPorcentaje', type: 'integer', minimum: 0, maximum: 9999),
        new OA\Property(property: 'tarifa', type: 'number', format: 'float'),
        new OA\Property(property: 'baseImponible', type: 'number', format: 'float'),
        new OA\Property(property: 'valor', type: 'number', format: 'float')
    ]
)]
class Impuesto {}

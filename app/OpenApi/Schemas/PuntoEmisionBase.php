<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'Punto de Emisión Base',
    description: 'Información básica de un punto de emisión',
    type: 'object',
    required: ['id', 'establecimiento_id', 'nombre', 'numero', 'ultimoSecuencial'],
    allOf: [
        new OA\Schema(
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 1, description: 'ID del punto de emisión'),
                new OA\Property(property: 'establecimiento_id', type: 'integer', example: 1, description: 'ID del establecimiento'),
                new OA\Property(property: 'nombre', type: 'string', example: 'Punto de emisión 1', description: 'Nombre del punto de emisión'),
                new OA\Property(property: 'numero', type: 'string', minLength: 3, maxLength: 3, example: '001', description: 'Código del punto de emisión'),
                new OA\Property(property: 'ultimoSecuencial', type: 'string', minLength: 9, maxLength: 9, example: '000000023', description: 'Último secuencial utilizado'),
            ]
        ),
        new OA\Schema(ref: '#/components/schemas/Timestamps'),
    ]
)]
class PuntoEmisionBase
{
}

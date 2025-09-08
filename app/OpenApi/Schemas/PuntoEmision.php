<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'Punto de Emisión',
    description: 'Información de un punto de emisión',
    type: 'object',
    required: ['max_secuenciales', 'estado'],
    allOf: [
        new OA\Schema(ref: '#/components/schemas/PuntoEmisionBase'),
        new OA\Schema(
            properties: [
                new OA\Property(property: 'max_secuenciales', type: 'string', minLength: 9, maxLength: 9, example: '999999999', description: 'Máximo secuencial permitido'),
                new OA\Property(property: 'estado', type: 'boolean', example: true, description: 'Estado del punto de emisión (activo/inactivo)'),
                new OA\Property(property: 'reset_at', type: 'string', format: 'date-time', nullable: true, description: 'Fecha y hora de la ultima vez que se reseteo el secuencial'),
            ]
        ),
    ]
)]
class PuntoEmision
{
}

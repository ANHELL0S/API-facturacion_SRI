<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'Establecimiento',
    description: 'Información completa de un establecimiento',
    type: 'object',
    required: ['id', 'user_id', 'nombre', 'numero'],
    allOf: [
        new OA\Schema(
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 1, description: 'ID del establecimiento'),
                new OA\Property(property: 'user_id', type: 'integer', example: 1, description: 'ID del usuario'),
                new OA\Property(property: 'nombre', type: 'string', example: 'Establecimiento 1', description: 'Nombre del establecimiento'),
                new OA\Property(property: 'numero', type: 'string', minLength: 3, maxLength: 3, example: '001', description: 'Código del establecimiento'),
                new OA\Property(property: 'direccion', type: 'string', example: 'Dirección del establecimiento', description: 'Dirección del establecimiento'),
            ]
        ),
        new OA\Schema(ref: '#/components/schemas/Timestamps'),
    ]
)]
class Establecimiento
{
}

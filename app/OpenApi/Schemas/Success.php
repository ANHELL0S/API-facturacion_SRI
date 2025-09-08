<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'Success',
    description: 'Estructura base de una respuesta exitosa',
    required: ['success', 'message'],
    type: 'object',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'message', type: 'string'),
        new OA\Property(property: 'data', type: 'object'),
        new OA\Property(property: 'errors', type: 'object'),
    ]
)]
class Success
{
}

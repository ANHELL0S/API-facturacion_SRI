<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'Error',
    description: 'Estructura base de una respuesta de error',
    required: ['success', 'message'],
    type: 'object',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: false),
        new OA\Property(property: 'message', type: 'string'),
        new OA\Property(property: 'data', type: 'object'),
        new OA\Property(property: 'errors', type: 'object'),
    ]
)]
class Error
{
}
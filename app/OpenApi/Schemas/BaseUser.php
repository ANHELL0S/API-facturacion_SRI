<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'BaseUser',
    description: 'Información básica del usuario',
    // required: ['name', 'email'],
    type: 'object',
    properties: [
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'email', type: 'string', format: 'email'),
    ]
)]
class BaseUser
{
}

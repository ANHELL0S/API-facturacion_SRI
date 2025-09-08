<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'RolePermission',
    description: 'Roles y/o permisos de usuario',
    required: ['id', 'name'],
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer'),
        new OA\Property(property: 'name', type: 'string'),
    ]
)]
class RolePermission
{
}

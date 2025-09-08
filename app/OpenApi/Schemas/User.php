<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'User',
    description: 'Esquema completo del usuario',
    type: 'object',
    allOf: [
        new OA\Schema(
            properties: [
                new OA\Property(property: 'id', type: 'integer'),
                new OA\Property(property: 'email_verified_at', type: 'datetime'),
                new OA\Property(property: 'active_account', type: 'boolean'),
            ]
        ),
        new OA\Schema(ref: '#/components/schemas/BaseUser'),
        new OA\Schema(ref: '#/components/schemas/Timestamps'),
        new OA\Schema(ref: '#/components/schemas/UserConfigInfo'),
        new OA\Schema(ref: '#/components/schemas/BillingInfo'),
    ]
)]
class User
{
}

<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'UserWithRoles',
    description: 'Usuario completo, información detallada',
    type: "object",
    allOf: [
        new OA\Schema(ref: '#/components/schemas/User'),
        new OA\Schema(
            properties: [
                // Roles de usuario
                new OA\Property(property: 'roles', type: 'array', items: new OA\Items(
                        allOf: [
                            new OA\Schema(ref: '#/components/schemas/RolePermission'),
                            new OA\Schema(
                                properties: [
                                    new OA\Property(
                                        property: 'permissions',
                                        type: 'array',
                                        items: new OA\Items(ref: '#/components/schemas/RolePermission')
                                    )
                                ]
                            )
                        ]
                    )
                ),
            ]
        )
    ]
)]
class UserWithRoles
{
}

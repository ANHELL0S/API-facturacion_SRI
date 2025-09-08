<?php

namespace App\OpenApi\Docs\Admin;

use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'Admin-Usuarios',
    description: 'Gestión administrativa de usuarios'
)]


// EJEMPLOS
#[OA\Examples(
    example:"NoPuedesRealizarEstaAccionSobreTiMismo",
    summary:"No puedes realizar esta acción sobre ti mismo",
    description:"No puedes realizar esta acción sobre ti mismo",
    value: [
        'success' => false,
        'message' => "No puedes realizar esta acción sobre ti mismo",
        'data' => [],
        'errors' => []
    ]
)]

class AdminUsersDocs
{
    /* ----------------------------- Listar usuarios ---------------------------- */
    #[OA\Get(
        path: '/admin/users',
        operationId: 'adminGetAllUsers',
        summary: 'Lista de usuarios registrados (*Paginado)',
        description: 'Obtener una lista de usuarios registrados.',
        tags: ['Admin-Usuarios'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Usuarios recuperados exitosamente',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/Success'),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: 'message',
                                    type: 'string',
                                    example: 'Usuarios recuperados exitosamente'
                                ),
                                new OA\Property(
                                    property: 'data',
                                    type: 'object',
                                    allOf: [
                                        new OA\Schema(ref: '#/components/schemas/PaginatedCollection'),
                                        new OA\Schema(
                                            properties: [
                                                new OA\Property(
                                                    property: 'data',
                                                    type: 'array',
                                                    items: new OA\Items(ref: '#/components/schemas/User')
                                                )
                                            ]
                                        )
                                    ]
                                )
                            ]
                        )
                    ],
                )
            ),
            new OA\Response(response: 401, ref: "#/components/responses/Unauthorized"),
            new OA\Response(
                response: 403,
                ref: "#/components/responses/Forbidden"
            ),
            new OA\Response(
                response: 500,
                description: 'No se pudo recuperar la lista de usuarios',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    private function index()
    {
        //
    }


    /* ----------------------------- Obtener un usuario ---------------------------- */
    #[OA\Get(
        path: '/admin/users/{user}',
        operationId: 'adminGetUser',
        summary: 'Obtener un usuario',
        description: 'Obtener un usuario con el id especificado.',
        tags: ['Admin-Usuarios'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'user',
                in: 'path',
                required: true,
                description: 'ID del usuario',
                schema: new OA\Schema(type: 'integer'),
                example: 1
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Usuario recuperado exitosamente',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/Success'),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: 'message',
                                    type: 'string',
                                    example: 'Usuario recuperado exitosamente'
                                ),
                                new OA\Property(
                                    property: 'data',
                                    type: 'object',
                                    ref: '#/components/schemas/User'
                                )
                            ]
                        )
                    ],
                )
            ),
            new OA\Response(response: 401, ref: "#/components/responses/Unauthorized"),
            new OA\Response(
                response: 403,
                description: 'Prohibido',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/Error',
                    examples: [
                        'CuentaInactiva' => new OA\Schema(ref: '#/components/examples/CuentaInactiva', example: 'CuentaInactiva'),
                        'AccesoDenegado' => new OA\Schema(ref: '#/components/examples/AccesoDenegado', example: 'AccesoDenegado'),
                        'NoPuedesRealizarEstaAccionSobreTiMismo' => new OA\Schema(ref: '#/components/examples/NoPuedesRealizarEstaAccionSobreTiMismo', example: 'NoPuedesRealizarEstaAccionSobreTiMismo')
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Usuario no encontrado o no es administrador',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OA\Response(
                response: 500,
                description: 'No se pudo recuperar el usuario',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    private function show()
    {
        //
    }


    /* ----------------------------- Crear un usuario ---------------------------- */
    #[OA\Post(
        path: '/admin/users',
        operationId: 'adminCreateUser',
        summary: 'Nuevo usuario administrador',
        description: 'Crear un nuevo usuario administrador',
        tags: ['Admin-Usuarios'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                title: "AdminCreateUser",
                description: "Datos necesarios para crear un nuevo usuario administrador",
                required: ['name', 'email', 'password', 'ambiente'],
                allOf: [
                    new OA\Schema(ref: '#/components/schemas/BaseUser'),
                    new OA\Schema(ref: '#/components/schemas/UserConfigInfo'),
                    new OA\Schema(ref: '#/components/schemas/BillingInfo'),
                    new OA\Schema(
                        properties: [
                            new OA\Property(
                                property: 'password',
                                type: 'string',
                                example: 'password'
                            )
                        ]
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Usuario creado exitosamente',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/Success'),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: 'message',
                                    type: 'string',
                                    example: 'Usuario creado exitosamente'
                                ),
                                new OA\Property(
                                    property: 'data',
                                    type: 'object',
                                    ref: '#/components/schemas/User'
                                )
                            ]
                        )
                    ],
                )
            ),
            new OA\Response(response: 401, ref: "#/components/responses/Unauthorized"),
            new OA\Response(
                response: 400,
                description: 'Datos no validos',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OA\Response(
                response: 403,
                ref: "#/components/responses/Forbidden"
            ),
            new OA\Response(
                response: 500,
                description: 'No se pudo crear el usuario',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    private function store()
    {
        //
    }


    /* ----------------------------- Actualizar un usuario ---------------------------- */
    #[OA\Put(
        path: '/admin/users/{user}',
        operationId: 'adminUpdateUser',
        summary: 'Actualizar un usuario administrador',
        tags: ['Admin-Usuarios'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'user',
                in: 'path',
                required: true,
                description: 'ID del usuario',
                schema: new OA\Schema(type: 'integer'),
                example: 1
            )
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                title: "AdminUpdateUser",
                allOf: [
                    new OA\Schema(ref: '#/components/schemas/BaseUser'),
                    new OA\Schema(ref: '#/components/schemas/UserConfigInfo'),
                    new OA\Schema(ref: '#/components/schemas/BillingInfo'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Usuario actualizado exitosamente',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/Success'),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: 'message',
                                    type: 'string',
                                    example: 'Usuario actualizado exitosamente'
                                ),
                                new OA\Property(
                                    property: 'data',
                                    type: 'object',
                                    ref: '#/components/schemas/User'
                                )
                            ]
                        )
                    ],
                )
            ),
            new OA\Response(response: 401, ref: "#/components/responses/Unauthorized"),
            new OA\Response(
                response: 400,
                description: 'Datos no validos',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OA\Response(
                response: 403,
                description: 'Prohibido',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/Error',
                    examples: [
                        'CuentaInactiva' => new OA\Schema(ref: '#/components/examples/CuentaInactiva', example: 'CuentaInactiva'),
                        'AccesoDenegado' => new OA\Schema(ref: '#/components/examples/AccesoDenegado', example: 'AccesoDenegado'),
                        'NoPuedesRealizarEstaAccionSobreTiMismo' => new OA\Schema(ref: '#/components/examples/NoPuedesRealizarEstaAccionSobreTiMismo', example: 'NoPuedesRealizarEstaAccionSobreTiMismo')
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Usuario no encontrado o no es administrador',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OA\Response(
                response: 500,
                description: 'No se pudo actualizar el usuario',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    private function update()
    {
        //
    }


    /* ----------------------------- Delete  usuario ---------------------------- */
    #[OA\Delete(
        path: '/admin/users/{user}',
        operationId: 'adminDeleteUser',
        summary: 'Eliminar un usuario administrador',
        tags: ['Admin-Usuarios'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'user',
                in: 'path',
                required: true,
                description: 'ID del usuario',
                schema: new OA\Schema(type: 'integer'),
                example: 1
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Usuario eliminado exitosamente',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/Success'),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: 'message',
                                    type: 'string',
                                    example: 'Usuario eliminado exitosamente'
                                )
                            ]
                        )
                    ],
                )
            ),
            new OA\Response(response: 401, ref: "#/components/responses/Unauthorized"),
            new OA\Response(
                response: 403,
                description: 'Prohibido',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/Error',
                    examples: [
                        'CuentaInactiva' => new OA\Schema(ref: '#/components/examples/CuentaInactiva', example: 'CuentaInactiva'),
                        'AccesoDenegado' => new OA\Schema(ref: '#/components/examples/AccesoDenegado', example: 'AccesoDenegado'),
                        'NoPuedesRealizarEstaAccionSobreTiMismo' => new OA\Schema(ref: '#/components/examples/NoPuedesRealizarEstaAccionSobreTiMismo', example: 'NoPuedesRealizarEstaAccionSobreTiMismo')
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Usuario no encontrado o no es administrador',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OA\Response(
                response: 500,
                description: 'No se pudo eliminar el usuario',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    private function destroy()
    {
        //
    }
}

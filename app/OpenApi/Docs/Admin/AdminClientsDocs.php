<?php

namespace App\OpenApi\Docs\Admin;

use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'Admin-Clientes',
    description: 'Gestión administrativa de clientes'
)]
class AdminClientsDocs
{
    /* ----------------------------- Listar clientes ---------------------------- */
    #[OA\Get(
        path: '/admin/clients',
        operationId: 'adminGetAllClients',
        summary: 'Lista de clientes registrados (*Paginado)',
        description: 'Obtener una lista de clientes registrados.',
        tags: ['Admin-Clientes'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Clientes recuperados exitosamente',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/Success'),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: 'message',
                                    type: 'string',
                                    example: 'Clientes recuperados exitosamente'
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
                description: 'No se pudo recuperar la lista de clientes',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    private function index()
    {
        //
    }


    /* ----------------------------- Obtener un cliente ---------------------------- */
    #[OA\Get(
        path: '/admin/clients/{client}',
        operationId: 'adminGetClient',
        summary: 'Obtener un cliente',
        description: 'Obtener un cliente con el id especificado.',
        tags: ['Admin-Clientes'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'client',
                in: 'path',
                required: true,
                description: 'ID del cliente',
                schema: new OA\Schema(type: 'integer'),
                example: 1
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Cliente recuperado exitosamente',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/Success'),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: 'message',
                                    type: 'string',
                                    example: 'Cliente recuperado exitosamente'
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
                ref: "#/components/responses/Forbidden"
            ),
            new OA\Response(
                response: 404,
                description: 'Usuario no encontrado o no es cliente',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OA\Response(
                response: 500,
                description: 'No se pudo recuperar el cliente',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    private function show()
    {
        //
    }


    /* ----------------------------- Crear un cliente ---------------------------- */
    #[OA\Post(
        path: '/admin/clients',
        operationId: 'adminCreateClient',
        summary: 'Nuevo usuario cliente',
        description: 'Crear un nuevo usuario cliente',
        tags: ['Admin-Clientes'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                title: "AdminCreateCliente",
                description: "Datos necesarios para crear un nuevo usuario cliente",
                required: ['name', 'email', 'password', 'ambiente', 'tarifa', 'ruc', 'razonSocial'],
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
                description: 'Cliente creado exitosamente',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/Success'),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: 'message',
                                    type: 'string',
                                    example: 'Cliente creado exitosamente'
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
                description: 'No se pudo crear el cliente',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    private function store()
    {
        //
    }


    /* ----------------------------- Actualizar un cliente ---------------------------- */
    #[OA\Put(
        path: '/admin/clients/{client}',
        operationId: 'adminUpdateCliente',
        summary: 'Actualizar usuario cliente',
        description: 'Actualizar un usuario cliente',
        tags: ['Admin-Clientes'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'client',
                in: 'path',
                required: true,
                description: 'ID del cliente',
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
                description: 'Cliente actualizado exitosamente',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/Success'),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: 'message',
                                    type: 'string',
                                    example: 'Cliente actualizado exitosamente'
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
                response: 404,
                description: 'Usuario no encontrado o no es cliente',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OA\Response(
                response: 500,
                description: 'No se pudo actualizar el cliente',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    private function update()
    {
        //
    }


    /* ----------------------------- Delete  cliente ---------------------------- */
    #[OA\Delete(
        path: '/admin/clients/{client}',
        operationId: 'adminDeleteClient',
        summary: 'Eliminar un usuario cliente',
        tags: ['Admin-Clientes'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'client',
                in: 'path',
                required: true,
                description: 'ID del cliente',
                schema: new OA\Schema(type: 'integer'),
                example: 1
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Cliente eliminado exitosamente',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/Success'),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: 'message',
                                    type: 'string',
                                    example: 'Cliente eliminado exitosamente'
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
                response: 404,
                description: 'Usuario no encontrado o no es cliente',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OA\Response(
                response: 500,
                description: 'No se pudo eliminar el cliente',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    private function destroy()
    {
        //
    }


    /* ----------------------------- Cargar certificado firma ---------------------------- */
    #[OA\Post(
        path: '/admin/clients/{client}/load_signature',
        operationId: 'adminLoadClientSignature',
        summary: 'Cargar certificado de firma y clave de cliente',
        tags: ['Admin-Clientes'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'client',
                in: 'path',
                required: true,
                description: 'ID del cliente',
                schema: new OA\Schema(type: 'integer'),
                example: 1
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['signature_file', 'signature_key'],
                    properties: [
                        new OA\Property(
                            property: 'signature_file',
                            type: 'string',
                            format: 'binary',
                            description: 'Archivo del certificado .p12'
                        ),
                        new OA\Property(
                            property: 'signature_key',
                            type: 'string',
                            description: 'Clave de firma del certificado'
                        )
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                 ref: "#/components/responses/SignatureLoadedResponse"
            ),
            new OA\Response(
                response: 401,
                ref: "#/components/responses/Unauthorized"
            ),
            new OA\Response(
                response: 403,
                ref: "#/components/responses/Forbidden"
            ),
            new OA\Response(
                response: 404,
                description: 'Usuario no encontrado o no es cliente',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            ),
            new OA\Response(
                response: 500,
                description: 'No se pudo cargar el certificado',
                content: new OA\JsonContent(ref: '#/components/schemas/Error')
            )
        ]
    )]
    private function load_signature()
    {
        //
    }
}
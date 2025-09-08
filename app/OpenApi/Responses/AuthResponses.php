<?php

namespace App\OpenApi\Responses;

use OpenApi\Attributes as OA;

class AuthResponses
{
    #[OA\Response(
        response: 'LoginSuccessResponse',
        description: 'Inicio de sesión exitoso',
        content: new OA\JsonContent(
            allOf: [
                new OA\Schema(ref: '#/components/schemas/Success'),
                new OA\Schema(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Inicio de sesión exitoso'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(
                                    property: 'token',
                                    type: 'string',
                                    description: 'Token de acceso',
                                    example: "lARja412sdAiuEQq13f..."
                                ),
                                new OA\Property(
                                    property: 'user_id',
                                    type: 'integer',
                                    description: 'ID del usuario',
                                    example: 1
                                ),
                                new OA\Property(
                                    property: 'token_expires_at',
                                    type: 'string',
                                    description: 'Fecha de expiración del token',
                                    example: '2023-08-31 23:59:59'
                                ),
                                new OA\Property(
                                    property: 'signature_expires_at',
                                    type: 'string',
                                    nullable: true,
                                    description: 'Fecha de expiración de la firma',
                                    example: '2023-08-31 23:59:59'
                                )
                            ]
                        )
                    ]
                )
            ]
        )
    )]
    public function login()
    {
        //
    }


    #[OA\Response(
        response: 'RefreshTokenSuccessResponse',
        description: 'Token renovado exitosamente',
        content: new OA\JsonContent(
            allOf: [
                new OA\Schema(ref: '#/components/schemas/Success'),
                new OA\Schema(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Token renovado exitosamente'
                        ),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(
                                    property: 'token',
                                    type: 'string',
                                    example: "lARja412sdAiuEQq13f..."
                                )
                            ]
                        )
                    ]
                )
            ]
        )
    )]
    public function refresh()
    {
        //
    }


    #[OA\Response(
        response: 'GetAuthenticatedUserResponse',
        description: 'Usuario recuperado exitosamente',
        content: new OA\JsonContent(
            allOf: [
                new OA\Schema(ref: '#/components/schemas/Success'),
                new OA\Schema(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Usuario recuperado exitosamente'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            ref: '#/components/schemas/UserWithRoles'
                        )
                    ]
                )
            ]
        )
    )]
    public function me()
    {
        //
    }


    #[OA\Response(
        response: 'LogoutSuccessResponse',
        description: 'Cierre de sesión exitoso',
        content: new OA\JsonContent(
            allOf: [
                new OA\Schema(ref: '#/components/schemas/Success'),
                new OA\Schema(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Cierre de sesión exitoso')
                    ]
                )
            ]
        )
    )]
    public function logout()
    {
        //
    }
}
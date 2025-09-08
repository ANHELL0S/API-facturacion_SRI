<?php

namespace App\OpenApi\Responses;

use OpenApi\Attributes as OA;

class PerfilClienteResponses
{
    #[OA\Response(
        response: 'PerfilShowResponse',
        description: 'Perfil recuperado exitosamente',
        content: new OA\JsonContent(
            allOf: [
                new OA\Schema(ref: '#/components/schemas/Success'),
                new OA\Schema(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Perfil recuperado exitosamente'),
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/User',
                        )
                    ]
                )
            ],
        )
    )]
    public function show()
    {
        //
    }


    #[OA\Response(
        response: 'PerfilUpdateResponse',
        description: 'Perfil actualizado exitosamente',
        content: new OA\JsonContent(
            allOf: [
                new OA\Schema(ref: '#/components/schemas/Success'),
                new OA\Schema(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Perfil actualizado exitosamente'),
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/User',
                        )
                    ]
                )
            ],
        )
    )]
    public function update()
    {
        //
    }


    #[OA\Response(
        response: 'PerfilPasswordChangeResponse',
        description: 'Contraseña cambiada exitosamente',
        content: new OA\JsonContent(
            allOf: [
                new OA\Schema(ref: '#/components/schemas/Success'),
                new OA\Schema(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Contraseña cambiada exitosamente'
                        )
                    ]
                )
            ]
        )
    )]
    public function updatePassword()
    {
        //
    }
}
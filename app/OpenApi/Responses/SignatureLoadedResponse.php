<?php

namespace App\OpenApi\Responses;

use OpenApi\Attributes as OA;

/* --------------------------------- Certificado y firma cargados -------------------------------- */
#[OA\Response(
    response: 'SignatureLoadedResponse',
    description: 'Certificado y clave de firma cargados exitosamente',
    content: new OA\JsonContent(
        allOf: [
            new OA\Schema(ref: '#/components/schemas/Success'),
            new OA\Schema(
                properties: [
                    new OA\Property(
                        property: 'message',
                        type: 'string',
                        example: 'Certificado y clave de firma cargados exitosamente'
                    ),
                    new OA\Property(
                        property: 'data',
                        type: 'object',
                        properties: [
                            new OA\Property(
                                property: 'expires_at',
                                type: 'datetime',
                                example: '2023-01-01 00:00:00'
                            )
                        ]
                    ),
                ]
            )
        ],
    )
)]

class SignatureLoadedResponse
{
}
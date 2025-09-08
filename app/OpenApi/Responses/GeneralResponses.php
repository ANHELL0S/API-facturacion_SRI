<?php

namespace App\OpenApi\Responses;

use AllowDynamicProperties;
use OpenApi\Attributes as OA;

/* --------------------------------- Success -------------------------------- */
#[OA\Response(
    response: 'Success',
    description: 'Success',
    content: new OA\JsonContent(ref: '#/components/schemas/Success')
)]

/* ---------------------------------- Error --------------------------------- */
#[OA\Response(
    response: 'Error',
    description: 'Error',
    content: new OA\JsonContent(ref: '#/components/schemas/Error')
)]



/* ---------------------------------- BadRequest --------------------------------- */
#[OA\Response(
    response: 'BadRequest',
    description: 'Solicitud incorrecta',
    content: new OA\JsonContent(
        allOf: [
            new OA\Schema(ref: '#/components/schemas/Error'),
            new OA\Schema(
                properties: [
                    new OA\Property(
                        property: 'message',
                        type: 'string',
                        example: 'Solicitud incorrecta'
                    )
                ]
            )
        ]
    )
)]

/* ------------------------------ Unauthorized ----------------------------- */
#[OA\Response(
    response: 'Unauthorized',
    description: 'No autorizado',
    content: new OA\JsonContent(
        allOf: [
            new OA\Schema(ref: '#/components/schemas/Error'),
            new OA\Schema(
                properties: [
                    new OA\Property(
                        property: 'message',
                        type: 'string',
                        example: 'No autorizado'
                    )
                ]
            )
        ],
        examples: [
            'NoAutorizado' => new OA\Schema(ref: '#/components/examples/NoAutorizado', example: 'NoAutorizado'),
            'TokenExpirado' => new OA\Schema(ref: '#/components/examples/TokenExpirado', example: 'TokenExpirado'),
            'TokenInvalido' => new OA\Schema(ref: '#/components/examples/TokenInvalido', example: 'TokenInvalido'),
            'TokenFaltante' => new OA\Schema(ref: '#/components/examples/TokenFaltante', example: 'TokenFaltante'),
        ]
    )
)]

/* ------------------------- Forbidden ------------------------- */
#[OA\Response(
    response: 'Forbidden',
    description: 'Prohibido',
    content: new OA\JsonContent(
        ref: '#/components/schemas/Error',
        examples: [
            'CuentaInactiva' => new OA\Schema(ref: '#/components/examples/CuentaInactiva', example: 'CuentaInactiva'),
            'AccesoDenegado' => new OA\Schema(ref: '#/components/examples/AccesoDenegado', example: 'AccesoDenegado'),
        ]
    )
)]

/* ------------------------- NotFOund ------------------------- */
#[OA\Response(
    response: 'NotFound',
    description: 'No encontrado',
    content: new OA\JsonContent(
        allOf: [
            new OA\Schema(ref: '#/components/schemas/Error'),
            new OA\Schema(
                properties: [
                    new OA\Property(
                        property: 'message',
                        type: 'string',
                        example: 'No encontrado'
                    )
                ]
            )
        ]
    )
)]

/* ------------------------- ValidationError ------------------------- */
#[OA\Response(
    response: 'ValidationError',
    description: 'Error de Validación',
    content: new OA\JsonContent(
        allOf: [
            new OA\Schema(ref: '#/components/schemas/Error'),
            new OA\Schema(
                properties: [
                    new OA\Property(
                        property: 'message',
                        type: 'string',
                        example: 'Error de validación'
                    )
                ]
            )
        ]
    )
)]

/* ---------------------------------- ServerError --------------------------------- */
#[OA\Response(
    response: 'ServerError',
    description: 'Error del servidor',
    content: new OA\JsonContent(
        allOf: [
            new OA\Schema(ref: '#/components/schemas/Error'),
            new OA\Schema(
                properties: [
                    new OA\Property(
                        property: 'message',
                        type: 'string',
                        example: 'Error del servidor'
                    )
                ]
            )
        ]
    )
)]
class GeneralResponses
{
}
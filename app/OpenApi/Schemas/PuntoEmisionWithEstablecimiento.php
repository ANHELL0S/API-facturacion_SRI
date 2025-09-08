<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'Punto de emisión con establecimiento',
    description: 'Información completa de un punto de emisión con establecimiento',
    type: 'object',
    allOf: [
        new OA\Schema(ref: '#/components/schemas/PuntoEmision'),
        new OA\Schema(
            properties: [
                new OA\Property(
                    property: 'establecimiento',
                    type: 'object',
                    ref: '#/components/schemas/Establecimiento'
                )
            ]
        )
    ]
)]
class PuntoEmisionWithEstablecimiento
{
}

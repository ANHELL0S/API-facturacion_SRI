<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'BillingInfo',
    description: 'Información de facturación del usuario',
    type: 'object',
    properties: [
        new OA\Property(property: 'ruc', type: 'string'),
        new OA\Property(property: 'razonSocial', type: 'string'),
        new OA\Property(property: 'nombreComercial', type: 'string'),
        new OA\Property(property: 'dirMatriz', type: 'string'),
        new OA\Property(property: 'contribuyenteEspecial', type: 'string'),
        new OA\Property(property: 'obligadoContabilidad', type: 'boolean'),
    ]
)]
class BillingInfo
{
}

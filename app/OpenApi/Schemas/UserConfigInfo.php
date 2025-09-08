<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;
use App\Enums\TarifasEnum;
use App\Enums\AmbientesEnum;

#[OA\Schema(
    title: 'UserConfigInfo',
    description: 'Configuración de ambiente y tarifa',
    type: 'object',
    // properties: [
    //     new OA\Property(property: 'tarifa', type: 'string', enum: ['comprobante', 'establecimiento']),
    //     new OA\Property(property: 'ambiente', type: 'integer', enum: [1,2]),
    // ]
)]
class UserConfigInfo
{
    #[OA\Property()]
    public TarifasEnum $tarifa;

    #[OA\Property()]
    public AmbientesEnum $ambiente;
}

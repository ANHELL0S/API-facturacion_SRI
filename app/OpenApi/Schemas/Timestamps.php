<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    properties: [
        new OA\Property(property: 'created_at', type: 'datetime'),
        new OA\Property(property: 'updated_at', type: 'datetime'),
    ]
)]
class Timestamps
{

}
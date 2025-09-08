<?php

namespace App\OpenApi\Responses;

use OpenApi\Attributes as OA;

class EstablecimientosResponses
{
    #[OA\Response(
        response: 'EstablecimientosIndexResponse',
        description: 'Establecimientos recuperados con éxito',
        content: new OA\JsonContent(
            allOf: [
                new OA\Schema(ref: '#/components/schemas/Success'),
                new OA\Schema(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Establecimientos recuperados con éxito'),
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
                                            items: new OA\Items(ref: '#/components/schemas/Establecimiento')
                                        )
                                    ]
                                )
                            ]
                        )
                    ]
                )
            ],
        )
    )]
    public function index()
    {
        //
    }


    #[OA\Response(
        response: 'EstablecimientoStoreResponse',
        description: 'Establecimiento creado exitosamente',
        content: new OA\JsonContent(
            allOf: [
                new OA\Schema(ref: '#/components/schemas/Success'),
                new OA\Schema(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Establecimiento creado exitosamente'),
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/Establecimiento',
                        )
                    ]
                )
            ],
        )
    )]
    public function store()
    {
        //
    }


    #[OA\Response(
        response: 'EstablecimientoShowResponse',
        description: 'Punto de emisión recuperado con éxito',
        content: new OA\JsonContent(
            allOf: [
                new OA\Schema(ref: '#/components/schemas/Success'),
                new OA\Schema(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Punto de emisión recuperado con éxito'),
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/Establecimiento',
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
        response: 'EstablecimientoUpdateResponse',
        description: 'Establecimiento actualizado exitosamente',
        content: new OA\JsonContent(
            allOf: [
                new OA\Schema(ref: '#/components/schemas/Success'),
                new OA\Schema(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Establecimiento actualizado exitosamente'),
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/Establecimiento',
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
        response: 'EstablecimientoDestroyResponse',
        description: 'Establecimiento eliminado exitosamente',
        content: new OA\JsonContent(
            allOf: [
                new OA\Schema(ref: '#/components/schemas/Success'),
                new OA\Schema(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Establecimiento eliminado exitosamente'),
                    ]
                )
            ],
        )
    )]
    public function destroy()
    {
        //
    }
}
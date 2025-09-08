<?php

namespace App\OpenApi\Responses;

use OpenApi\Attributes as OA;

class PuntosEmisionResponses
{
    #[OA\Response(
        response: 'PuntoEmisionIndexResponse',
        description: 'Operación exitosa',
        content: new OA\JsonContent(
            allOf: [
                new OA\Schema(ref: '#/components/schemas/Success'),
                new OA\Schema(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Puntos de emisión recuperados con éxito'),
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
                                            items: new OA\Items(ref: '#/components/schemas/PuntoEmision')
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
        response: 'PuntoEmisionStoreResponse',
        description: 'Punto de emisión creado exitosamente',
        content: new OA\JsonContent(
            allOf: [
                new OA\Schema(ref: '#/components/schemas/Success'),
                new OA\Schema(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Punto de emisión creado exitosamente'),
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/PuntoEmisionBase',
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
        response: 'PuntoEmisionShowResponse',
        description: 'Punto de emisión recuperado con éxito',
        content: new OA\JsonContent(
            allOf: [
                new OA\Schema(ref: '#/components/schemas/Success'),
                new OA\Schema(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Punto de emisión recuperado con éxito'),
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/PuntoEmisionWithEstablecimiento',
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
        response: 'PuntoEmisionUpdateResponse',
        description: 'Punto de emisión actualizado exitosamente',
        content: new OA\JsonContent(
            allOf: [
                new OA\Schema(ref: '#/components/schemas/Success'),
                new OA\Schema(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Punto de emisión actualizado exitosamente'),
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/PuntoEmisionWithEstablecimiento',
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
        response: 'PuntoEmisionDestroyResponse',
        description: 'Punto de emisión eliminado exitosamente',
        content: new OA\JsonContent(
            allOf: [
                new OA\Schema(ref: '#/components/schemas/Success'),
                new OA\Schema(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Punto de emisión eliminado exitosamente'),
                    ]
                )
            ],
        )
    )]
    public function destroy()
    {
        //
    }


    #[OA\Response(
        response: 'PuntoEmisionResetResponse',
        description: 'Punto de emisión reiniciado exitosamente',
        content: new OA\JsonContent(
            allOf: [
                new OA\Schema(ref: '#/components/schemas/Success'),
                new OA\Schema(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Punto de emisión reiniciado exitosamente'),
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/PuntoEmisionWithEstablecimiento',
                        )
                    ]
                )
            ],
        )
    )]
    public function reset()
    {
        //
    }
}
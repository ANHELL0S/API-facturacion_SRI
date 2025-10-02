<?php

namespace App\OpenApi\Responses;

use OpenApi\Attributes as OA;

class ComprobantesResponses
{
    #[OA\Response(
        response: 'ListarComprobantesResponse',
        description: 'Comprobantes recuperados exitosamente',
        content: new OA\JsonContent(
            allOf: [
                new OA\Schema(ref: '#/components/schemas/Success'),
                new OA\Schema(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Comprobantes recuperados exitosamente'
                        ),
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
                                            items: new OA\Items(ref: '#/components/schemas/Comprobante')
                                        )
                                    ]
                                )
                            ]
                        )
                    ]
                )
            ]
        )
    )]
    public function listarComprobantes()
    {
        //
    }


    #[OA\Response(
        response: 'ObtenerComprobanteResponse',
        description: 'Comprobante recuperado exitosamente',
        content: new OA\JsonContent(
            allOf: [
                new OA\Schema(ref: '#/components/schemas/Success'),
                new OA\Schema(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Comprobante recuperado exitosamente'
                        ),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            ref: '#/components/schemas/Comprobante'
                        )
                    ]
                )
            ]
        )
    )]
    public function consultarComprobante()
    {
        //
    }


    #[OA\Response(
        response: 'ObtenerEstadoComprobanteResponse',
        description: 'Estado del comprobante obtenido exitosamente',
        content: new OA\JsonContent(
            allOf: [
                new OA\Schema(ref: '#/components/schemas/Success'),
                new OA\Schema(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Estado del comprobante obtenido correctamente.'
                        ),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'estado', type: 'string', example: 'autorizado')
                            ]
                        )
                    ]
                )
            ]
        )
    )]
    public function getEstadoComprobante()
    {
        //
    }


    #[OA\Response(
        response: 'ObtenerXmlComprobanteResponse',
        description: 'XML obtenido exitosamente',
        content: new OA\JsonContent(
            allOf: [
                new OA\Schema(ref: '#/components/schemas/Success'),
                new OA\Schema(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'XML obtenido exitosamente'
                        ),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'xml', type: 'string')
                            ]
                        )
                    ]
                )
            ]
        )
    )]
    public function consultarXmlComprobante()
    {
        //
    }


    #[OA\Response(
        response: 'ComprobanteNoAutorizadoResponse',
        description: 'Comprobante no autorizado',
        content: new OA\JsonContent(
            allOf: [
                new OA\Schema(ref: '#/components/schemas/Error'),
                new OA\Schema(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Comprobante no autorizado'
                        ),
                        new OA\Property(
                            property: 'errors',
                            type: 'array',
                            items: new OA\Items(type: "string", example: "No es posible obtener el XML porque el comprobante no ha sido autorizado por el SRI")
                        )
                    ]
                )
            ]
        )
    )]
    public function comprobanteNoAutorizado()
    {
        //
    }


    #[OA\Response(
        response: 'ProcesandoComprobanteResponse',
        description: 'Tu comprobante se está procesando. Recibirás una notificación cuando esté listo',
        content: new OA\JsonContent(
            allOf: [
                new OA\Schema(ref: '#/components/schemas/Success'),
                new OA\Schema(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Tu comprobante se está procesando. Recibirás una notificación cuando esté listo'
                        )
                    ]
                )
            ]
        )
    )]
    public function generarFactura()
    {
        //
    }
}
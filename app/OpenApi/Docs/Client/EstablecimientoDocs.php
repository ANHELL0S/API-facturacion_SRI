<?php

namespace App\OpenApi\Docs\Client;

use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'Establecimientos',
    description: 'Endpoints relacionados con establecimientos'
)]
class EstablecimientoDocs
{
    /* ------------------------------- Listar establecimientos ------------------------------- */
    #[OA\Get(
        path: '/establecimientos',
        operationId: 'listEstablecimientos',
        summary: 'Obtener lista de establecimientos **(Paginado)**',
        description: <<<EOT
        Retorna una lista de establecimientos del cliente autenticado. **(Paginado)**

        Respuestas esperadas:
        - 200: Establecimientos recuperados con éxito
        - 500: Error al recuperar los establecimientos
        EOT,
        tags: ['Establecimientos'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'page',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'per_page',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/EstablecimientosIndexResponse'),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
            new OA\Response(response: 500, ref: '#/components/responses/ServerError'),
        ]
    )]
    private function index()
    {
    }

    /* ------------------------------- Crear establecimiento ------------------------------- */
    #[OA\Post(
        path: '/establecimientos',
        operationId: 'createEstablecimiento',
        summary: 'Crear un nuevo establecimiento',
        description: <<<EOT
        Crear un nuevo establecimiento con los datos proporcionados.

        El campo **numero** es opcional. Si no se proporciona, se asignará automáticamente un número incremental comenzando desde **"001"**.

        Respuestas esperadas:
        - 201: Establecimiento creado exitosamente
        - 422: Datos no válidos
        - 500: Error al crear el establecimiento
        EOT,
        tags: ['Establecimientos'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['nombre', 'direccion'],
                properties: [
                    new OA\Property(property: 'nombre', type: 'string', example: 'Establecimiento 1', description: 'Nombre del establecimiento'),
                    new OA\Property(property: 'direccion', type: 'string', example: 'Calle Falsa 123', description: 'Dirección del establecimiento'),
                    new OA\Property(property: 'numero', type: 'string', example: '001', minLength: 3, maxLength: 3, description: 'Código del establecimiento (opcional)')
                ],
            )
        ),
        responses: [
            new OA\Response(response: 201, ref: '#/components/responses/EstablecimientoStoreResponse',),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
            new OA\Response(response: 422, ref: '#/components/responses/ValidationError'),
            new OA\Response(response: 500, ref: '#/components/responses/ServerError'),
        ]
    )]
    private function store()
    {
    }

    /* ------------------------------- Mostrar establecimiento ------------------------------- */
    #[OA\Get(
        path: '/establecimientos/{establecimiento}',
        operationId: 'showEstablecimiento',
        summary: 'Obtener un establecimiento por ID',
        description: <<<EOT
        Retorna un establecimiento basado en su ID.

        Respuestas esperadas:
        - 200: Establecimiento recuperado con éxito
        - 403: Acceso Denegado
        - 404: Recurso no encontrado
        - 500: Error al recuperar el establecimiento
        EOT,
        tags: ['Establecimientos'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'establecimiento',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/EstablecimientoShowResponse',),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
            new OA\Response(response: 500, ref: '#/components/responses/ServerError'),
        ]
    )]
    private function show()
    {
    }

    /* ------------------------------- Actualizar establecimiento ------------------------------- */
    #[OA\Put(
        path: '/establecimientos/{establecimiento}',
        operationId: 'updateEstablecimiento',
        summary: 'Actualizar un establecimiento existente',
        description: <<<EOT
        Actualizar un establecimiento existente con los datos proporcionados.

        Respuestas esperadas:
        - 200: Establecimiento actualizado exitosamente
        - 403: Acceso Denegado
        - 404: Recurso no encontrado
        - 422: Datos no válidos
        - 500: Error al actualizar el establecimiento
        EOT,
        tags: ['Establecimientos'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'establecimiento',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'nombre', type: 'string', example: 'Establecimiento Actualizado', description: 'Nombre del establecimiento'),
                    new OA\Property(property: 'direccion', type: 'string', example: 'Calle Verdadera 321', description: 'Dirección del establecimiento'),
                    new OA\Property(property: 'numero', type: 'string', example: '001', minLength: 3, maxLength: 3, description: 'Código del establecimiento')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/EstablecimientoUpdateResponse',),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
            new OA\Response(response: 422, ref: '#/components/responses/ValidationError'),
            new OA\Response(response: 500, ref: '#/components/responses/ServerError'),
        ]
    )]
    private function update()
    {
    }

    /* ------------------------------- Eliminar establecimiento ------------------------------- */
    #[OA\Delete(
        path: '/establecimientos/{establecimiento}',
        operationId: 'deleteEstablecimiento',
        summary: 'Eliminar un establecimiento',
        description: <<<EOT
        Eliminar un establecimiento existente.

        Respuestas esperadas:
        - 200: Establecimiento eliminado exitosamente
        - 403: Acceso Denegado
        - 404: Recurso no encontrado
        - 500: Error al eliminar el establecimiento
        EOT,
        tags: ['Establecimientos'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'establecimiento',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/EstablecimientoDestroyResponse',),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
            new OA\Response(response: 500, ref: '#/components/responses/ServerError'),
        ]
    )]
    private function destroy()
    {
    }
}

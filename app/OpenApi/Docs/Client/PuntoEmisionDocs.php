<?php

namespace App\OpenApi\Docs\Client;

use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'Puntos de Emision',
    description: 'Control de puntos de emisión del usuario autenticado'
)]
class PuntoEmisionDocs
{
    /* ------------------------------- Listar puntos de emisión ------------------------------- */
    #[OA\Get(
        path: '/puntos-emision',
        operationId: 'listPuntosEmision',
        summary: 'Obtener lista de puntos de emisión **(Paginado)**',
        description: <<<EOT
        Retorna una lista de puntos de emisión asociados al usuario autenticado. **(Paginado)**

        Respuestas esperadas:
        - 200: Puntos de emisión recuperados con éxito
        - 404: No se encontraron establecimientos registrados
        - 500: Error al recuperar los puntos de emisión
        EOT,
        tags: ['Puntos de Emision'],
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
            new OA\Parameter(
                name: 'establecimiento_id',
                description: 'ID del establecimiento del cual se desea obtener los puntos de emisión',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'estado',
                description: 'Estado de los puntos de emisión: 1 = Activo, 0 = Inactivo',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/PuntoEmisionIndexResponse'),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
            new OA\Response(response: 500, ref: '#/components/responses/ServerError'),
        ]
    )]
    private function index()
    {
    }

    /* ------------------------------- Crear punto de emisión ------------------------------- */
    #[OA\Post(
        path: '/puntos-emision',
        operationId: 'createPuntoEmision',
        summary: 'Crear un nuevo punto de emisión',
        description: <<<EOT
        Crear un nuevo punto de emisión con los datos proporcionados.

        El campo **numero** es opcional. Si no se proporciona, se asignará automáticamente un número incremental comenzando desde **"001"**.

        El campo **ultimoSecuencial** siempre se inicializa en **"000000000"** para cada nuevo punto de emisión.

        Respuestas esperadas:
        - 400: El numero proporcionado ('NUMERO') ya existe
        - 201: Punto de emisión creado exitosamente
        - 403: Acceso denegado
        - 422: Datos no válidos
        - 500: Error al crear el punto de emisión
        EOT,
        tags: ['Puntos de Emision'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['establecimiento_id', 'nombre'],
                properties: [
                    new OA\Property(property: 'establecimiento_id', type: 'integer', description: 'ID del establecimiento al que pertenece el punto de emisión'),
                    new OA\Property(property: 'nombre', type: 'string', example: 'Punto de Emisión 1', description: 'Nombre del punto de emisión'),
                    new OA\Property(property: 'numero', type: 'string', example: '001', minLength: 3, maxLength: 3, description: 'Código del punto de emisión (opcional)')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, ref: '#/components/responses/PuntoEmisionStoreResponse'),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
            new OA\Response(response: 500, ref: '#/components/responses/ServerError'),
        ]
    )]
    private function store()
    {
    }

    /* ------------------------------- Mostrar punto de emisión ------------------------------- */
    #[OA\Get(
        path: '/puntos-emision/{punto_emision}',
        operationId: 'showPuntoEmision',
        summary: 'Obtener un punto de emisión por ID',
        description: <<<EOT
        Retorna un punto de emisión específico basado en su ID.

        Respuestas esperadas:
        - 200: Punto de emisión recuperado con éxito
        - 403: Acceso Denegado
        - 404: Recurso no encontrado
        - 500: Error al recuperar el punto de emisión
        EOT,
        tags: ['Puntos de Emision'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'punto_emision',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/PuntoEmisionShowResponse'),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
            new OA\Response(response: 500, ref: '#/components/responses/ServerError'),
        ]
    )]
    private function show()
    {
    }

    /* ------------------------------- Actualizar punto de emisión ------------------------------- */
    #[OA\Put(
        path: '/puntos-emision/{punto_emision}',
        operationId: 'updatePuntoEmision',
        summary: 'Actualizar un punto de emisión existente',
        description: <<<EOT
        Actualizar un punto de emisión existente con los datos proporcionados.

        El campo **numero** es opcional.
        El campo **ultimoSecuencial** es opcional.

        Respuestas esperadas:
        - 200: Punto de emisión actualizado exitosamente
        - 403: Acceso denegado
        - 404: Recurso no encontrado
        - 422: Datos no válidos
        - 500: Error al actualizar el punto de emisión
        EOT,
        tags: ['Puntos de Emision'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'punto_emision',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['nombre'],
                properties: [
                    new OA\Property(property: 'nombre', type: 'string', example: 'Punto de Emisión Actualizado', description: 'Nombre del punto de emisión'),
                    new OA\Property(property: 'numero', type: 'string', example: '002', minLength: 3, maxLength: 3, description: 'Código del punto de emisión (opcional)'),
                    new OA\Property(property: 'ultimoSecuencial', type: 'string', example: '000000005', description: 'Secuencial del punto de emisión (opcional)')
                ],
            )
        ),
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/PuntoEmisionUpdateResponse'),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
            new OA\Response(response: 500, ref: '#/components/responses/ServerError'),
        ]
    )]
    private function update()
    {
    }

    /* ------------------------------- Eliminar punto de emisión ------------------------------- */
    #[OA\Delete(
        path: '/puntos-emision/{id}',
        operationId: 'deletePuntoEmision',
        summary: 'Eliminar un punto de emisión',
        description: <<<EOT
        Eliminar un punto de emisión por ID.

        Respuestas esperadas:
        - 200: Punto de emisión eliminado exitosamente
        - 403: Acceso denegado
        - 404: Recurso no encontrado
        - 500: Error al eliminar el punto de emisión
        EOT,
        tags: ['Puntos de Emision'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/PuntoEmisionDestroyResponse'),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
            new OA\Response(response: 500, ref: '#/components/responses/ServerError'),
        ]
    )]
    private function destroy()
    {
    }


    /* ------------------------------- Reiniciar punto de emisión ------------------------------- */
    #[OA\Post(
        path: '/puntos-emision/reset/{id}',
        operationId: 'resetPuntoEmision',
        summary: 'Reiniciar un punto de emisión',
        description: <<<EOT
        Reinicia los secuenciales de un punto de emisión especifico.

        Respuestas esperadas:
        - 200: Punto de emisión reiniciado exitosamente
        - 403: Acceso denegado
        - 404: Recurso no encontrado
        - 500: Error al reiniciar el punto de emisión
        EOT,
        tags: ['Puntos de Emision'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/PuntoEmisionResetResponse'),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
            new OA\Response(response: 500, ref: '#/components/responses/ServerError'),
        ]
    )]
    private function reset()
    {
    }
}

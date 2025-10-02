<?php

namespace App\OpenApi\Docs\Client;

use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'Comprobantes',
    description: 'Consultar, generar y firmar comprobantes electrónicos'
)]
class ComprobantesDocs
{
    /* ------------------------------- Listar comprobantes ------------------------------- */
    #[OA\Get(
        path: '/comprobantes',
        operationId: 'listarComprobantes',
        summary: 'Listar comprobantes emitidos **(Paginado)**',
        description: <<<EOT
        Obtiene una lista paginada de los comprobantes filtrados por tipo, estado y fechas. **(Paginado)**

        Respuestas esperadas:
        - 200: Comprobantes recuperados exitosamente
        - 401: No autorizado
        - 403: Acceso denegado
        - 422: Parámetros no válidos
        - 500: Error al recuperar los comprobantes
        EOT,
        tags: ['Comprobantes'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'tipo',
                in: 'query',
                description: 'Tipo de comprobante (opcional)',
                required: false,
                schema: new OA\Schema(type: 'string', example: '01')
            ),
            new OA\Parameter(
                name: 'ambiente',
                in: 'query',
                description: 'Ambiente del comprobante (opcional)',
                required: false,
                schema: new OA\Schema(type: 'string', example: '1')
            ),
            new OA\Parameter(
                name: 'estado',
                in: 'query',
                description: 'Estado del comprobante (opcional)',
                required: false,
                schema: new OA\Schema(type: 'string', example: 'autorizado')
            ),
            new OA\Parameter(
                name: 'fecha_desde',
                in: 'query',
                description: 'Fecha de emisión desde (opcional)',
                required: false,
                schema: new OA\Schema(type: 'string', format: 'date', example: '2024-04-01')
            ),
            new OA\Parameter(
                name: 'fecha_hasta',
                in: 'query',
                description: 'Fecha de emisión hasta (opcional)',
                required: false,
                schema: new OA\Schema(type: 'string', format: 'date', example: '2024-04-30')
            ),
            new OA\Parameter(
                name: 'per_page',
                in: 'query',
                description: 'Resultados por página (opcional)',
                required: false,
                schema: new OA\Schema(type: 'integer', example: 10)
            ),
            new OA\Parameter(
                name: 'page',
                in: 'query',
                description: 'Número de página (opcional)',
                required: false,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/ListarComprobantesResponse'),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
            new OA\Response(response: 422, ref: '#/components/responses/ValidationError'),
            new OA\Response(response: 500, ref: '#/components/responses/ServerError'),
        ]
    )]
    private function listarComprobantes()
    {
    }


    /* ------------------------------- Consultar comprobante por clave de acceso ------------------------------- */
    #[OA\Get(
        path: '/comprobantes/{clave_acceso}',
        operationId: 'obtenerComprobante',
        summary: 'Obtener un comprobante específico',
        description: <<<EOT
        Consulta un comprobante por su clave de acceso.

        Respuestas esperadas:
        - 200: Comprobante recuperado exitosamente
        - 401: No autorizado
        - 403: Acceso denegado
        - 404: Comprobante no encontrado
        - 500: Error al recuperar el comprobante
        EOT,
        tags: ['Comprobantes'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'clave_acceso',
                in: 'path',
                required: true,
                description: 'Clave de acceso del comprobante',
                schema: new OA\Schema(type: 'string', minLength: 49, maxLength: 49, example: '2204202501025017927200110010010000000073104300715')
            )
        ],
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/ObtenerComprobanteResponse'),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
            new OA\Response(response: 500, ref: '#/components/responses/ServerError'),
        ]
    )]
    private function consultarComprobante()
    {
    }


    /* ------------------------------- Consultar estado del comprobante ------------------------------- */
    #[OA\Get(
        path: '/comprobantes/{clave_acceso}/estado',
        operationId: 'obtenerEstadoComprobante',
        summary: 'Obtener el estado de un comprobante',
        description: <<<EOT
        Consulta el estado de un comprobante por su clave de acceso.

        Respuestas esperadas:
        - 200: Estado del comprobante obtenido exitosamente
        - 401: No autorizado
        - 403: Acceso denegado
        - 404: Comprobante no encontrado
        - 500: Error inesperado
        EOT,
        tags: ['Comprobantes'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'clave_acceso',
                in: 'path',
                required: true,
                description: 'Clave de acceso del comprobante',
                schema: new OA\Schema(type: 'string', minLength: 49, maxLength: 49, example: '2204202501025017927200110010010000000073104300715')
            )
        ],
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/ObtenerEstadoComprobanteResponse'),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
            new OA\Response(response: 500, ref: '#/components/responses/ServerError'),
        ]
    )]
    private function consultarEstadoComprobante()
    {
    }


    /* ------------------------------- Consultar XML del comprobante ------------------------------- */
    #[OA\Get(
        path: '/comprobantes/{clave_acceso}/xml',
        operationId: 'obtenerXmlComprobante',
        summary: 'Obtener el XML de un comprobante',
        description: <<<EOT
        Consulta el XML de un comprobante por su clave de acceso.

        Respuestas esperadas:
        - 200: XML obtenido exitosamente
        - 401: No autorizado
        - 403: Acceso denegado:
            - No tienes permiso para ver este comprobante
            - El comprobante tiene un error registrado
        - 404: Comprobante no encontrado
        - 409: Comprobante no autorizado
        - 500: Error inesperado al consultar el XML
        - 502: Error de consulta en el SRI
        EOT,
        tags: ['Comprobantes'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'clave_acceso',
                in: 'path',
                required: true,
                description: 'Clave de acceso del comprobante',
                schema: new OA\Schema(type: 'string', minLength: 49, maxLength: 49, example: '2204202501025017927200110010010000000073104300715')
            )
        ],
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/ObtenerXmlComprobanteResponse'),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
            new OA\Response(response: 409, ref: '#/components/responses/ComprobanteNoAutorizadoResponse'),
            new OA\Response(response: 500, ref: '#/components/responses/ServerError'),
            new OA\Response(response: 502, ref: '#/components/responses/ServerError'),
        ]
    )]
    private function consultarXmlComprobante()
    {
    }


    /* ------------------------------- Generar Factura ------------------------------- */
    #[OA\Post(
        path: '/comprobantes/factura/{punto_emision}',
        operationId: 'generarFactura',
        summary: 'Generar factura',
        description: <<<EOT
        Genera una factura y la procesa en cola.
        **El cliente puede:**
        - Consultar el estado de la factura
        - Conectarse al webhook de la factura y recibir notificaciones cuando el comprobante haya sido procesado

        Respuestas esperadas:
        - 200: Tu comprobante se está procesando. Recibirás una notificación cuando esté listo
        - 401: No autorizado
        - 403: Acceso denegado:
            - El usuario no tiene un certificado de firma digital cargado
            - El certificado de firma digital expiro
        - 404: Punto de emisión no encontrado
        - 500: Error al generar la factura **(Devuelve request data)**
        EOT,
        tags: ['Comprobantes'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'punto_emision',
                in: 'path',
                required: true,
                description: 'ID del punto de emisión',
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            description: 'Datos de la factura',
            content: new OA\JsonContent(
                ref: '#/components/schemas/FacturaRequest'
            )
        ),
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/ProcesandoComprobanteResponse'),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
            new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
            new OA\Response(response: 500, ref: '#/components/responses/ServerError'),
        ]
    )]
    private function generarFactura()
    {
    }
}

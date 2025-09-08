<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'Comprobante',
    description: 'Información del comprobante electrónico emitido',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid', example: 'f47ac10b-58cc-4372-a567-0e02b2c3d479'),
        new OA\Property(property: 'tipo_comprobante', type: 'integer', example: 1, description: 'Tipo de comprobante (por ejemplo: factura, nota de crédito)'),
        new OA\Property(property: 'establecimiento', type: 'string', maxLength: 3, nullable: true, example: '001'),
        new OA\Property(property: 'punto_emision', type: 'string', maxLength: 3, nullable: true, example: '001'),
        new OA\Property(property: 'secuencial', type: 'string', maxLength: 9, nullable: true, example: '000000123'),
        new OA\Property(property: 'clave_acceso', type: 'string', maxLength: 49, nullable: true, example: '2204202501025017927200110010010000000012345678901'),
        new OA\Property(property: 'ambiente', type: 'integer', example: 1, description: 'Ambiente (1 = pruebas, 2 = producción)'),
        new OA\Property(property: 'cliente_email', type: 'string', format: 'email', nullable: true, example: 'cliente@example.com'),
        new OA\Property(property: 'cliente_ruc', type: 'string', maxLength: 13, nullable: true, example: '0999999999001'),
        new OA\Property(property: 'estado', type: 'string', example: 'autorizado', description: 'Estado actual del comprobante'),
        new OA\Property(property: 'error_code', type: 'string', nullable: true, example: '45', description: 'Código de error del SRI si aplica'),
        new OA\Property(property: 'error_message', type: 'string', nullable: true, example: 'ERROR SECUENCIAL REGISTRADO', description: 'Descripción del error si existe'),
        new OA\Property(property: 'fecha_emision', type: 'string', format: 'date-time', example: '2025-04-24T10:20:30Z'),
        new OA\Property(property: 'procesado_en', type: 'string', format: 'date-time', nullable: true, example: '2025-04-24T10:25:00Z'),
        new OA\Property(property: 'fecha_autorizacion', type: 'string', format: 'date-time', nullable: true, example: '2025-04-24T10:30:00Z'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2025-04-24T10:00:00Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2025-04-24T10:30:00Z'),
    ]
)]
class Comprobante
{
}

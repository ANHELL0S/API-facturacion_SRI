<?php

namespace App\OpenApi\Docs\Client;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'API Documentation',
    version: '1.0.0',
    description: <<<EOT
Esta documentación describe los endpoints disponibles para clientes consumidores de la API de Facturación.

## Autenticación
Para obtener un token de acceso, utiliza el endpoint `/api/login` con tus credenciales (correo y contraseña).

## Documentación para Administración
Consulta la documentación de los endpoints para clientes administradores en: [API de Administración](/admin-api/documentation).
EOT,
)]

#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT'
)]
class Server
{
}
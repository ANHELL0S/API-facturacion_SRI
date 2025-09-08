<?php

namespace App\OpenApi\Docs\Admin;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'Admin API Documentation',
    version: '1.0.0',
    description: <<<EOT
Esta documentación describe los endpoints disponibles para administradores de la API de Facturación.

## Autenticación
Para obtener un token de acceso, utiliza el endpoint `/api/login` con tus credenciales (correo y contraseña).

## Documentación para Clientes
Consulta la documentación de los endpoints para clientes consumidores en: [API de Clientes](/api/documentation).
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
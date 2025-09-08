<?php

namespace App\OpenApi\Examples;

use OpenApi\Attributes as OA;

#[OA\Examples(
    example: 'NoAutorizado',
    summary: 'No Autorizado',
    description: 'Usuario en el token no encontrado',
    value: [
        'success' => false,
        'message' => 'No autorizado',
        'data' => [],
        'errors' => ['Usuario no encontrado']
    ]
)]

#[OA\Examples(
    example: 'TokenExpirado',
    summary: 'Token expirado',
    description: 'El token ha expirado',
    value: [
        'success' => false,
        'message' => 'Token expirado',
        'data' => [],
        'errors' => []
    ]
)]

#[OA\Examples(
    example:"TokenInvalido",
    summary:"Token invalido",
    description:"El token ya no es valido (no es posible refrescarlo)",
    value: [
        'success' => false,
        'message' => "Token invalido",
        'data' => [],
        'errors' => []
    ]
)]

#[OA\Examples(
    example:"TokenFaltante",
    summary:"Token faltante",
    description:"No se ha enviado un token en la cabecera",
    value: [
        'success' => false,
        'message' => "Token faltante",
        'data' => [],
        'errors' => []
    ]
)]

#[OA\Examples(
    example:"CuentaInactiva",
    summary:"Cuenta inactiva",
    description:"La cuenta del usuario autenticado no esta activa",
    value: [
        'success' => false,
        'message' => "Cuenta inactiva",
        'data' => [],
        'errors' => []
    ]
)]

#[OA\Examples(
    example:"AccesoDenegado",
    summary:"Acceso denegado por permisos insuficientes",
    description:"El usuario no cuenta con los permisos necesarios para realizar la acción",
    value: [
        'success' => false,
        'message' => "Acceso Denegado",
        'data' => [],
        'errors' => []
    ]
)]
class General
{
}
<?php

namespace App\OpenApi\Docs\Auth;

use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'Auth',
    description: 'Endpoints relacionados con autenticación'
)]
class AuthDocs
{
    /* ------------------------------- Login user ------------------------------- */
    #[OA\Post(
        path: '/login',
        operationId: 'login',
        summary: 'Login',
        description: <<<EOT
        Verificar al usuario y generar un token de acceso.

        Respuestas esperadas:
        - 200: Login exitoso
        - 400: Error en el token
        - 401: Credenciales inválidas
        - 500: No fue posible generar el token
        EOT,
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(
                        property: 'email',
                        type: 'string',
                        format: 'email',
                        description: 'Email del usuario',
                        example: 'admin@example.com'
                    ),
                    new OA\Property(
                        property: 'password',
                        type: 'string',
                        format: 'password',
                        description: 'Password del usuario',
                        example: '123456789'
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, ref: "#/components/responses/LoginSuccessResponse"),
            new OA\Response(response: 400, ref: "#/components/responses/BadRequest"),
            new OA\Response(response: 401, ref: "#/components/responses/Unauthorized"),
            new OA\Response(response: 500, ref: "#/components/responses/ServerError"),
        ]
    )]
    private function login()
    {
    }


    /* ------------------------------- Refresh token ------------------------------- */
    #[OA\Post(
        path: '/refresh-token',
        operationId: 'refreshToken',
        summary: 'Refrescar token',
        description: <<<EOT
        Refrescar token de usuario mientras no haya superado el tiempo de renovación.

        Respuestas esperadas:
        - 200: Token renovado correctamente
        - 400: El token ha expirado y no puede renovarse
        - 401: No autorizado
        - 500: Error al renovar el token
        EOT,
        tags: ['Auth'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, ref: "#/components/responses/RefreshTokenSuccessResponse"),
            new OA\Response(response: 400, ref: "#/components/responses/BadRequest"),
            new OA\Response(response: 401, ref: "#/components/responses/Unauthorized"),
            new OA\Response(response: 500, ref: "#/components/responses/ServerError"),
        ]
    )]
    private function refresh()
    {
    }


    /* ------------------------- Get authenticated user ------------------------- */
    #[OA\Get(
        path: '/me',
        operationId: 'me',
        summary: 'Obtener mi información',
        description: <<<EOT
        Obtener la colección del usuario autenticado.

        Respuestas esperadas:
        - 200: Usuario recuperado exitosamente
        - 401: No autorizado
        - 404: Usuario no encontrado
        - 500: Error al recuperar el usuario
        EOT,
        tags: ['Auth'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, ref: "#/components/responses/GetAuthenticatedUserResponse"),
            new OA\Response(response: 400, ref: "#/components/responses/BadRequest"),
            new OA\Response(response: 401, ref: "#/components/responses/Unauthorized"),
            new OA\Response(response: 404, ref: "#/components/responses/NotFound"),
            new OA\Response(response: 500, ref: "#/components/responses/ServerError"),
        ]
    )]
    private function me()
    {
    }


    /* ------------------------------- Logout user ------------------------------ */
    #[OA\Post(
        path: '/logout',
        operationId: 'logout',
        summary: 'Cierre de sesión',
        description: <<<EOT
        Cerrar sesión del usuario denegando el token.

        Respuestas esperadas:
        - 200: Cierre de sesión exitoso
        - 401: No autorizado
        - 500: Error al cerrar sesión
        EOT,
        tags: ['Auth'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, ref: "#/components/responses/LogoutSuccessResponse"),
            new OA\Response(response: 401, ref: "#/components/responses/Unauthorized"),
            new OA\Response(response: 500, ref: "#/components/responses/ServerError"),
        ]
    )]
    private function logout()
    {
    }
}

<?php

namespace App\OpenApi\Docs\Client;

use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'Perfil',
    description: 'Gestión del perfil del cliente'
)]
class PerfilClientDocs
{
    /* ----------------------------- Obtener perfil de cliente ---------------------------- */
    #[OA\Get(
        path: '/profile',
        operationId: 'getProfile',
        summary: 'Obtener perfil del cliente',
        description: <<<EOT
        Obtener los detalles del perfil del cliente autenticado.

        Respuestas esperadas:
        - 200: Perfil recuperado exitosamente
        - 401: No autorizado
        - 403: Acceso Denegado
        - 500: Error al recuperar el perfil del cliente
        EOT,
        tags: ['Perfil-Cliente'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/PerfilShowResponse',),
            new OA\Response(response: 401, ref: "#/components/responses/Unauthorized"),
            new OA\Response(response: 403, ref: "#/components/responses/Forbidden"),
            new OA\Response(response: 500, ref: "#/components/responses/ServerError"),
        ]
    )]
    private function show()
    {
        //
    }

    /* ----------------------------- Actualizar perfil de cliente ---------------------------- */
    #[OA\Put(
        path: '/profile',
        operationId: 'updateProfile',
        summary: 'Actualizar perfil de cliente',
        description: <<<EOT
        Actualizar los detalles del perfil del cliente autenticado.

        Respuestas esperadas:
        - 200: Perfil actualizado exitosamente
        - 401: No autorizado
        - 403: Acceso Denegado
        - 422: Datos no válidos
        - 500: Error al actualizar el perfil del cliente
        EOT,
        tags: ['Perfil-Cliente'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'John Doe'),
                    new OA\Property(property: 'razonSocial', type: 'string', example: 'ABC Company'),
                    new OA\Property(property: 'nombreComercial', type: 'string', example: 'ABC Company'),
                    new OA\Property(property: 'dirMatriz', type: 'string', example: 'Calle Principal 123'),
                    new OA\Property(property: 'contribuyenteEspecial', type: 'string', example: '123456789'),
                    new OA\Property(property: 'obligadoContabilidad', type: 'boolean', example: false),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/PerfilUpdateResponse',),
            new OA\Response(response: 401, ref: "#/components/responses/Unauthorized"),
            new OA\Response(response: 403, ref: "#/components/responses/Forbidden"),
            new OA\Response(response: 422, ref: "#/components/responses/ValidationError"),
            new OA\Response(response: 500, ref: "#/components/responses/ServerError"),
        ]
    )]
    private function update()
    {
        //
    }

    /* ----------------------------- Cambiar contraseña de cliente ---------------------------- */
    #[OA\Put(
        path: '/profile/password',
        operationId: 'changeClientPassword',
        summary: 'Cambiar la contraseña del cliente',
        description: <<<EOT
        Permite al cliente cambiar su contraseña.

        La contraseña actual debe ser correcta y la nueva contraseña debe tener al menos 8 caracteres.

        Respuestas esperadas:
        - 200: Contraseña cambiada exitosamente
        - 401: No autorizado
        - 403: Acceso Denegado
        - 422: Datos no válidos
        - 500: Error al cambiar la contraseña del cliente
        EOT,
        tags: ['Perfil-Cliente'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                title: "PerfilClienteChangePassword",
                required: ['current_password', 'password'],
                properties: [
                    new OA\Property(
                        property: 'current_password',
                        type: 'string',
                        description: 'Contraseña actual del cliente. Debe ser válida.',
                        example: 'currentPassword123'
                    ),
                    new OA\Property(
                        property: 'password',
                        type: 'string',
                        description: 'Nueva contraseña que debe tener al menos 8 caracteres y ser confirmada.',
                        example: 'newPassword456'
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/PerfilUpdateResponse',),
            new OA\Response(response: 401, ref: "#/components/responses/Unauthorized"),
            new OA\Response(response: 403, ref: "#/components/responses/Forbidden"),
            new OA\Response(response: 422, ref: "#/components/responses/ValidationError"),
            new OA\Response(response: 500, ref: "#/components/responses/ServerError"),
        ]
    )]
    private function updatePassword()
    {
        //
    }


    /* ----------------------------- Actualizar certificado de firma ---------------------------- */
    #[OA\Post(
        path: '/profile/signature',
        operationId: 'updateClientSignature',
        summary: 'Actualizar certificado de firma digital del cliente',
        description: <<<EOT
        Permite al cliente actualizar su certificado de **firma digital (.p12)** y su clave de firma asociada.

        El archivo del certificado y la clave son necesarios para completar la actualización.

        Respuestas esperadas:
        - 200: Certificado de firma actualizado exitosamente
        - 401: No autorizado
        - 403: Acceso Denegado
        - 500: Error al actualizar el certificado de firma digital del cliente
        EOT,
        tags: ['Perfil-Cliente'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['signature_file', 'signature_key'],
                    properties: [
                        new OA\Property(
                            property: 'signature_file',
                            type: 'string',
                            format: 'binary',
                            description: 'Archivo de certificado de firma digital en formato .p12'
                        ),
                        new OA\Property(
                            property: 'signature_key',
                            type: 'string',
                            description: 'Clave de firma asociada al certificado .p12'
                        )
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, ref: "#/components/responses/SignatureLoadedResponse"),
            new OA\Response(response: 401, ref: "#/components/responses/Unauthorized"),
            new OA\Response(response: 403, ref: "#/components/responses/Forbidden"),
            new OA\Response(response: 500, ref: "#/components/responses/ServerError"),
        ]
    )]
    private function uploadProfileSignatureFile()
    {
        //
    }
}

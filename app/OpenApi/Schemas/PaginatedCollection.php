<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'PaginatedCollection',
    description: 'Esquema de colección paginada',
    type: 'object',
    properties: [
        new OA\Property(
            property: 'current_page',
            type: 'integer',
            description: 'Página actual',
            example: 1
        ),
        new OA\Property(
            property: 'data',
            type: 'array',
            description: 'Lista de objetos en la página actual.',
            items: new OA\Items()
        ),
        new OA\Property(
            property: 'first_page_url',
            type: 'string',
            description: 'URL de la primera página.',
        ),
        new OA\Property(
            property: 'from',
            type: 'integer',
            nullable: true,
            description: 'Primer elemento de la página actual.',
            example: null
        ),
        new OA\Property(
            property: 'last_page',
            type: 'integer',
            description: 'Última página disponible.',
            example: 1
        ),
        new OA\Property(
            property: 'last_page_url',
            type: 'string',
            description: 'URL de la última página.',
        ),
        new OA\Property(
            property: 'links',
            type: 'array',
            description: 'Lista de enlaces de paginado.',
            items: new OA\Items(
                type: 'object',
                properties: [
                    new OA\Property(
                        property: 'url',
                        type: 'string',
                        nullable: true,
                        description: 'URL del enlace (o null si no está disponible).',
                        example: null
                    ),
                    new OA\Property(
                        property: 'label',
                        type: 'string',
                        description: 'Etiqueta del enlace.',
                        example: '&laquo; Previous'
                    ),
                    new OA\Property(
                        property: 'active',
                        type: 'boolean',
                        description: 'Indica si el enlace está activo.',
                        example: false
                    ),
                ]
            )
        ),
        new OA\Property(
            property: 'next_page_url',
            type: 'string',
            nullable: true,
            description: 'URL de la siguiente página.',
            example: null
        ),
        new OA\Property(
            property: 'path',
            type: 'string',
            description: 'Ruta base para la paginado',
            example: 'http://localhost:8000/api/admin/users'
        ),
        new OA\Property(
            property: 'per_page',
            type: 'integer',
            description: 'Número de elementos por página.',
            example: 15
        ),
        new OA\Property(
            property: 'prev_page_url',
            type: 'string',
            nullable: true,
            description: 'URL de la página anterior.',
            example: null
        ),
        new OA\Property(
            property: 'to',
            type: 'integer',
            nullable: true,
            description: 'Último elemento de la página actual.',
            example: null
        ),
        new OA\Property(
            property: 'total',
            type: 'integer',
            description: 'Total de elementos disponibles.',
            example: 0
        ),
    ]
)]
class PaginatedCollection
{
}

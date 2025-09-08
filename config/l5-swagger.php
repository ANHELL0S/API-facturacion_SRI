<?php

return [
    'default' => 'default',

    'documentations' => [
        'default' => [
            'api' => [
                'title' => 'API Documentation',
            ],
            'routes' => [
                /*
                 * Ruta para acceder a la interfaz de documentación de API
                 */
                'api' => 'api/documentation',

                /*
                 * Ruta para acceder a las anotaciones analizadas de swagger.
                 */
                'docs' => 'docs',

                /*
                 * Ruta para la devolución de llamada de autenticación Oauth2.
                 */
                'oauth2_callback' => 'api/oauth2-callback',
            ],
            'paths' => [
                /*
                 * Edita para incluir URL completa en UI para assets
                 */
                'use_absolute_path' => env('L5_SWAGGER_USE_ABSOLUTE_PATH', true),

                /*
                 * Nombre del archivo de documentación JSON generado
                 */
                'docs_json' => 'api-docs.json',

                /*
                 * Nombre del archivo de documentación YAML generado
                 */
                'docs_yaml' => 'api-docs.yaml',

                /*
                 * Configura esto a `json` o `yaml` para determinar qué archivo de documentación usar en UI
                 */
                'format_to_use_for_docs' => env('L5_FORMAT_TO_USE_FOR_DOCS', 'json'),

                /*
                 * Ruta absoluta a la ubicación donde se almacenarán las anotaciones analizadas
                 */
                'docs' => storage_path('api-docs'),

                /*
                 * Ruta absoluta al directorio donde exportar vistas
                 */
                'views' => base_path('resources/views/vendor/l5-swagger'),

                /*
                 * Edita para establecer la ruta base de la API
                 */
                'base' => env('L5_SWAGGER_BASE_PATH', null),

                /*
                 * Edita para establecer la ruta donde se deben almacenar los activos de swagger ui
                 */
                'swagger_ui_assets_path' => env('L5_SWAGGER_UI_ASSETS_PATH', 'vendor/swagger-api/swagger-ui/dist/'),

                /*
                 * Rutas absolutas al directorio que contiene las anotaciones swagger.
                 */
                'annotations' => [
                    base_path('app/OpenApi/Docs/Client'),
                    base_path('app/OpenApi/Docs/Auth'),
                    base_path('app/OpenApi/Schemas'),
                    base_path('app/OpenApi/Responses'),
                    base_path('app/OpenApi/Examples'),
                ],

                /*
                 * Ruta absoluta a directorios que deberían ser excluidos del escaneo
                 */
                'excludes' => [],
            ],
        ],
        'admin' => [
            'api' => [
                'title' => 'Admin API Documentation',
            ],
            'routes' => [
                'api' => 'admin-api/documentation',

                'docs' => 'admin-docs',

                'oauth2_callback' => 'admin-api/oauth2-callback',
            ],
            'paths' => [
                'use_absolute_path' => env('L5_SWAGGER_USE_ABSOLUTE_PATH', true),
                'docs_json' => 'api-docs-admin.json',
                'docs_yaml' => 'api-docs-admin.yaml',
                'format_to_use_for_docs' => env('L5_FORMAT_TO_USE_FOR_DOCS', 'json'),

                'docs' => storage_path('api-docs'),
                'views' => base_path('resources/views/vendor/l5-swagger'),
                'base' => env('L5_SWAGGER_BASE_PATH', null),
                'swagger_ui_assets_path' => env('L5_SWAGGER_UI_ASSETS_PATH', 'vendor/swagger-api/swagger-ui/dist/'),

                'annotations' => [
                    base_path('app/OpenApi/Docs/Admin'),
                    base_path('app/OpenApi/Docs/Auth'),
                    base_path('app/OpenApi/Schemas'),
                    base_path('app/OpenApi/Responses'),
                    base_path('app/OpenApi/Examples'),
                ],

                'excludes' => [],
            ],
        ],
    ],
    'defaults' => [
        'routes' => [
            /*
             * Ruta para acceder a las anotaciones analizadas de swagger.
             */
            'docs' => 'docs',

            /*
             * Ruta para la devolución de llamada de autenticación Oauth2.
             */
            'oauth2_callback' => 'api/oauth2-callback',

            /*
             * Middleware permite prevenir acceso inesperado a la documentación de API
             */
            'middleware' => [
                'api' => ['web', 'swagger.auth'],
                'asset' => ['web', 'swagger.auth'],
                'docs' => ['web', 'swagger.auth'],
                'oauth2_callback' => [],
            ],

            /*
             * Opciones de grupo de rutas
             */
            'group_options' => [],
        ],

        'paths' => [
            /*
             * Ruta absoluta a la ubicación donde se almacenarán las anotaciones analizadas
             */
            'docs' => storage_path('api-docs'),

            /*
             * Ruta absoluta al directorio donde exportar vistas
             */
            'views' => base_path('resources/views/vendor/l5-swagger'),

            /*
             * Edita para establecer la ruta base de la API
             */
            'base' => env('L5_SWAGGER_BASE_PATH', null),

            /*
             * Edita para establecer la ruta donde se deben almacenar los activos de swagger ui
             */
            'swagger_ui_assets_path' => env('L5_SWAGGER_UI_ASSETS_PATH', 'vendor/swagger-api/swagger-ui/dist/'),

            /*
             * Ruta absoluta a directorios que deberían ser excluidos del escaneo
             */
            'excludes' => [],
        ],

        'scanOptions' => [
            /**
             * Configuración para procesadores predeterminados. Permite pasar configuración de procesadores a swagger-php.
             */
            'default_processors_configuration' => [],

            /**
             * analizador: por defecto \OpenApi\StaticAnalyser.
             */
            'analyser' => null,

            /**
             * análisis: por defecto un nuevo \OpenApi\Analysis.
             */
            'analysis' => null,

            /**
             * Clases de procesadores de rutas de consulta personalizadas.
             */
            'processors' => [],

            /**
             * patrón: string $pattern Patrones de archivo para escanear (predeterminado: *.php).
             */
            'pattern' => null,

            /*
             * Ruta absoluta a directorios que deberían ser excluidos del escaneo
             */
            'exclude' => [],

            /*
             * Permite generar especificaciones para OpenAPI 3.0.0 o OpenAPI 3.1.0.
             * Por defecto la especificación será en versión 3.0.0
             */
            'open_api_spec_version' => env('L5_SWAGGER_OPEN_API_SPEC_VERSION', \L5Swagger\Generator::OPEN_API_DEFAULT_SPEC_VERSION),
        ],

        'securityDefinitions' => [
            'securitySchemes' => [
                'bearerAuth' => [
                    'type' => 'http',
                    'scheme' => 'bearer',
                    'bearerFormat' => 'JWT'
                ],
            ],
            'security' => [
                [],
            ],
        ],

        /*
         * Configura esto a `true` en modo desarrollo para que los documentos se regeneren en cada solicitud
         * Configura esto a `false` para deshabilitar la generación de swagger en producción
         */
        'generate_always' => env('L5_SWAGGER_GENERATE_ALWAYS', false),

        /*
         * Configura esto a `true` para generar una copia de la documentación en formato yaml
         */
        'generate_yaml_copy' => env('L5_SWAGGER_GENERATE_YAML_COPY', false),

        /*
         * Edita para confiar en la dirección IP del proxy - necesario para AWS Load Balancer
         */
        'proxy' => false,

        /*
         * Configs plugin permite obtener configuraciones externas en lugar de pasarlas a SwaggerUIBundle.
         */
        'additional_config_url' => null,

        /*
         * Aplica un orden a la lista de operaciones de cada API.
         */
        'operations_sort' => env('L5_SWAGGER_OPERATIONS_SORT', null),

        /*
         * Pasa el parámetro validatorUrl a la inicialización de SwaggerUi en el lado JS.
         * Un valor nulo aquí deshabilita la validación.
         */
        'validator_url' => null,

        /*
         * Parámetros de configuración de Swagger UI
         */
        'ui' => [
            'display' => [
                'dark_mode' => env('L5_SWAGGER_UI_DARK_MODE', false),
                /*
                 * Controla la configuración de expansión predeterminada para las operaciones y etiquetas.
                 */
                'doc_expansion' => env('L5_SWAGGER_UI_DOC_EXPANSION', 'none'),

                /**
                 * Si se establece, habilita el filtrado.
                 */
                'filter' => env('L5_SWAGGER_UI_FILTERS', true),

                /**
                 * Si se establece, muestra el ID de la operación.
                 */
                'operation_id' => env('L5_SWAGGER_UI_OPERATION_ID', false),

                /**
                 * Si se establece, muestra el tiempo de respuesta de la operación en ms.
                 */
                'request_duration' => env('L5_SWAGGER_UI_REQUEST_DURATION', false),

                'syntax_highlight' => [
                    /*
                     * Si se establece en true, habilita el resaltado de sintaxis.
                     */
                    'activated' => env('L5_SWAGGER_UI_SYNTAX_HIGHLIGHTER', true),

                    /*
                     * Si se establece, establece el tema del resaltado.
                     */
                    'theme' => env('L5_SWAGGER_UI_SYNTAX_HIGHLIGHTER_THEME', 'arta'),
                ],
            ],

            'authorization' => [
                /*
                 * Si se establece en true, persiste los datos de autorización
                 */
                'persist_authorization' => env('L5_SWAGGER_UI_PERSIST_AUTHORIZATION', true),

                'oauth2' => [
                    /*
                     * Si se establece en true, agrega PKCE al flujo AuthorizationCodeGrant
                     */
                    'use_pkce_with_authorization_code_grant' => false,
                ],
            ],
        ],
        /*
         * Constantes que pueden usarse en anotaciones
         */
        'constants' => [
            'L5_SWAGGER_CONST_HOST' => env('L5_SWAGGER_CONST_HOST', 'http://my-default-host.com'),
        ],
    ],
];

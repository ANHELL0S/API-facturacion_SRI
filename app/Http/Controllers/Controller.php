<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponse;

/**
 * @OA\Info(
 *  title="API de Facturación",
 *  version="1.0.0",
 *  description="Documentación swagger de la API de Facturación",
 * )
 *
 * @OA\SecurityScheme(
 *  type="http",
 *  name="Authorization",
 *  in="header",
 *  scheme="bearer",
 *  bearerFormat="JWT",
 *  securityScheme="bearerAuth"
 * )
 */
abstract class Controller
{
    use ApiResponse;
}

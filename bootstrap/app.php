<?php

use App\Traits\ApiResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\SwaggerAuthenticate;
use App\Http\Middleware\ForceJsonResponse;
use App\Http\Middleware\JwtMiddleware;
use App\Http\Middleware\CheckActiveAccount;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        api: __DIR__ . '/../routes/api.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Middleware to force JSON responses
        $middleware->alias([
            'swagger.auth' => SwaggerAuthenticate::class,
            'json.response' => ForceJsonResponse::class,
            'jwt' => JwtMiddleware::class,
            'check.active.account' => CheckActiveAccount::class,
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->renderable(function (Exception $e) {
            if ($e instanceof \Illuminate\Validation\ValidationException) {
                Log::error('Error de validación: ' . $e->getMessage());
                return ApiResponse::sendError('Datos no válidos', $e->errors(), 422);
            }
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                Log::error('Recurso no encontrado: ' . $e->getMessage());
                return ApiResponse::sendError('Recurso no encontrado', 'El recurso solicitado no existe o no está disponible', 404);
            }
            if ($e instanceof UnauthorizedException) {
                Log::error('Acceso no autorizado: ' . $e->getMessage());
                return ApiResponse::sendError('Acceso no autorizado', 'No tienes permiso para acceder a este recurso', 403);
            }
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
                Log::error('Excepción HTTP: ' . $e->getMessage());
                return ApiResponse::sendError('Error de solicitud HTTP', 'Ha ocurrido un error en la solicitud', $e->getStatusCode());
            }

            Log::error('Error de servidor: ' . $e->getMessage());
            return ApiResponse::sendError('Error de servidor', 'Ha ocurrido un error en el servidor', 500);
        });
    })->create();

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\JWTAuthController;
use App\Http\Controllers\Admin\AdminUsersController;
use App\Http\Controllers\Admin\AdminClientsController;
use App\Http\Controllers\Client\ComprobantesController;
use App\Http\Controllers\Client\EstablecimientoController;
use App\Http\Controllers\Client\PuntoEmisionController;
use App\Http\Controllers\Client\PerfilClientController;

Route::group(['middleware' => ['json.response']], function () {
    /* ---------------------------------- Auth routes ---------------------------------- */
    Route::post('register', [JWTAuthController::class, 'register'])->name('register');
    Route::post('login', [JWTAuthController::class, 'login'])->name('login');
    Route::middleware('jwt')->group(function () {
        Route::post('refresh-token', [JWTAuthController::class, 'refresh'])->name('refresh-token');
        Route::get('me', [JWTAuthController::class, 'me']);
        Route::post('logout', [JWTAuthController::class, 'logout']);
    });

    /* ---------------------------------- ADMIN routes ---------------------------------- */
    Route::prefix('admin')->middleware(['jwt', 'role:admin'])->group(function () {
        Route::resource('users', AdminUsersController::class)->only([
            'index',
            'show',
            'store',
            'update',
            'destroy'
        ]);

        Route::resource('clients', AdminClientsController::class)->only([
            'index',
            'show',
            'store',
            'update',
            'destroy'
        ]);
        Route::post('clients/{client}/load_signature', [AdminClientsController::class, 'load_signature']);
    });

    /* ---------------------------------- Perfil de Usuario ---------------------------------- */
    Route::middleware(['jwt'])->group(function () {
        // Rutas de perfil
        Route::get('profile', [PerfilClientController::class, 'show']);
        Route::put('profile', [PerfilClientController::class, 'update']);
        Route::put('profile/password', [PerfilClientController::class, 'updatePassword']);
        Route::post('profile/signature', [PerfilClientController::class, 'updateSignature']);
        Route::post('profile/logo', [PerfilClientController::class, 'updateLogo']);
    });

    /* ---------------------------------- Rutas Establecimientos ---------------------------------- */
    Route::middleware(['jwt', 'role:client'])->group(function () {
        Route::get('/establecimientos', [EstablecimientoController::class, 'index']);
        Route::post('/establecimientos', [EstablecimientoController::class, 'store']);
        Route::get('/establecimientos/{establecimiento}', [EstablecimientoController::class, 'show']);
        Route::put('/establecimientos/{establecimiento}', [EstablecimientoController::class, 'update']);
        Route::delete('/establecimientos/{establecimiento}', [EstablecimientoController::class, 'destroy']);
    });

    /* ---------------------------------- Rutas Puntos de Emisiones ---------------------------------- */
    Route::middleware(['jwt', 'role:client'])->group(function () {
        Route::get('/puntos-emision', [PuntoEmisionController::class, 'index']);
        Route::post('/puntos-emision', [PuntoEmisionController::class, 'store']);
        Route::get('/puntos-emision/{punto_emision}', [PuntoEmisionController::class, 'show']);
        Route::put('/puntos-emision/{punto_emision}', [PuntoEmisionController::class, 'update']);
        Route::delete('/puntos-emision/{punto_emision}', [PuntoEmisionController::class, 'destroy']);
        Route::put('/puntos-emision/{punto_emision}/secuencial', [PuntoEmisionController::class, 'updateSecuencial']);
        /* --------------------------- Rutas para Reseteo --------------------------- */
        Route::post('/puntos-emision/reset/{punto_emision}', [PuntoEmisionController::class, 'reset']);
    });

    /* ---------------------------------- Rutas de comprobantes ---------------------------------- */
    Route::prefix('comprobantes')->middleware(['jwt', 'role:client'])->group(function () {
        Route::get('/', [ComprobantesController::class, 'index']);
        Route::get('/export/authorized', [ComprobantesController::class, 'exportAuthorized']);

        Route::get('/byId/{id}', [ComprobantesController::class, 'showById']);
        Route::get('/byId/{id}/xml', [ComprobantesController::class, 'getXmlById']);
        Route::get('/byId/{id}/pdf', [ComprobantesController::class, 'getPdfById']);
        Route::get('/byId/{id}/estado', [ComprobantesController::class, 'getEstadoById']);
        Route::post('/byId/{id}/resend-webhook', [ComprobantesController::class, 'resendWebhookById']);

        Route::get('/{clave_acceso}', [ComprobantesController::class, 'show']);
        Route::get('/{clave_acceso}/estado', [ComprobantesController::class, 'getEstado']);
        Route::get('/{clave_acceso}/xml', [ComprobantesController::class, 'getXml']);
        Route::get('/{clave_acceso}/pdf', [ComprobantesController::class, 'getPdf'])->name('comprobantes.pdf');
        // Route::get('/{clave_acceso}/anular', [ComprobantesController::class, 'show']);

        Route::post('/factura/{punto_emision}', [ComprobantesController::class, 'generateFactura']);
        Route::post('/descargar-masivo', [ComprobantesController::class, 'descargarMasivo']);
        Route::get('/descargar-masivo/{jobId}/status', [ComprobantesController::class, 'getBulkDownloadStatus']);
        Route::get('/descargar-masivo/{jobId}/download', [ComprobantesController::class, 'downloadBulkZip']);
        // Route::post('/notas-credito/{punto_emision_id}/emitir', [FacturaController::class, 'generateNotaCredito']);
        // Route::post('/retenciones/{punto_emision_id}/emitir', [FacturaController::class, 'generateRetencion']);
    });
});

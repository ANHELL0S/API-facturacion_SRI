<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    public static function sendResponse(string $message = "Success", array|object|null $data=null, int $code=200, string|array|null $errors=null): JsonResponse
    {
        // Asegurarse de que los errores sean un array
        $errors = is_string($errors) ? [$errors] : $errors;

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data ?? [],
            'errors' => $errors ?? []
        ], $code);
    }

    public static function sendError(string $error="Unexpected error", string|array|null $errorMessages=null, int $code=404, array|object|null $data=null): JsonResponse
    {
        // Convertir string JSON a array si es necesario
        if (is_string($errorMessages)) {
            $decoded = json_decode($errorMessages, true);
            $errorMessages = json_last_error() === JSON_ERROR_NONE ? $decoded : [$errorMessages];
        }

        return response()->json([
            'success' => false,
            'message' => $error,
            'data' => $data ?? [],
            'errors' => $errorMessages ?? []
        ], $code);
    }
}

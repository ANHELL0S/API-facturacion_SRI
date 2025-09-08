<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class JwtMiddleware
{
    use ApiResponse;

    /**
     * Summary of handle
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user) {
                return $this->sendError('No autorizado', ['Usuario no encontrado'], 401);
            }

            // Verificar si la cuenta está activa
            if (!$user->active_account) {
                return $this->sendError('Cuenta inactiva', null, 403);
            }
        } catch (TokenExpiredException $e) {
            // Permitir acceso solo a la ruta de refresh si el token ha expirado
            if ($request->routeIs('refresh-token')) {
                return $next($request); // Permitir acceso a la ruta de refresh
            }

            return $this->sendError('Token expirado', $e->getMessage(), 401);
        } catch (TokenInvalidException $e) {
            return $this->sendError('Token invalido', $e->getMessage(), 401);
        } catch (JWTException $e) {
            return $this->sendError('Token faltante', $e->getMessage(), 401);
        }

        return $next($request);
    }
}

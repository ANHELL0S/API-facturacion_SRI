<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Enums\RolesEnum;
use App\Enums\TarifasEnum;
use App\Enums\AmbientesEnum;

class JWTAuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'email' => 'required|email|unique:users',
                'password' => 'required|string|min:8',
                'ruc' => 'required|string|regex:/^[0-9]{13}$/|unique:users,ruc',
                'razonSocial' => 'required|string',
                'nombreComercial' => 'string',
                'dirMatriz' => 'string',
                'obligadoContabilidad' => 'boolean',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Datos no validos', $validator->errors(), 400);
            }

            $validated = $validator->validated();
            $validated['password'] = bcrypt($validated['password']);
            $validated['tarifa'] = TarifasEnum::COMPROBANTE->value;
            $validated['ambiente'] = AmbientesEnum::PRUEBAS->value;
            $validated['active_account'] = false;

            $user = User::create($validated);
            $user->assignRole(RolesEnum::CLIENTE);

            return $this->sendResponse('Usuario registrado exitosamente', [], 201);
        } catch (\Exception $e) {
            return $this->sendError('No se pudo registrar el usuario', $e->getMessage(), 500);
        }
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        try {
            // Attempt to verify the credentials and create a token for the user
            if (! $token = JWTAuth::attempt($credentials)) {
                return $this->sendError('Credenciales inválidos', null, 401);
            }

            // Get the authenticated user.
            $user = auth()->user();

            // Verify if the account is active
            if (!$user->active_account) {
                return $this->sendError('Usuario desactivado', null, 403);
            }

            // Genera un nuevo token con claims personalizados
            $token = JWTAuth::claims(['role' => $user->role])->fromUser($user);

            // Extrae el payload desde el string del token
            $payload = JWTAuth::setToken($token)->getPayload();

            return $this->sendResponse('Inicio de sesión exitoso', [
                'token' => $token,
                'user_id' => $user->id,
                'token_expires_at' => Carbon::createFromTimestamp($payload->get('exp'))->toDateTimeString(),
                'signature_expires_at' => $user->signature_expires_at
            ]);
        } catch (JWTException $e) {
            return $this->sendError('Error en el token', $e->getMessage(), 400);
        } catch (\Exception $e) {
            return $this->sendError('No fue posible generar un token', $e->getMessage(), 500);
        }
    }


    public function refresh()
    {
        try {
            // Obtener un nuevo token basado en el actual
            $newToken = JWTAuth::refresh(JWTAuth::getToken());

            // Devuelve el nuevo token
            return $this->sendResponse('Token renovado exitosamente', ['token' => $newToken]);
        } catch (TokenExpiredException $e) {
            return $this->sendError('El token ha expirado y no puede renovarse', null, 400);
        } catch (\Exception $e) {
            return $this->sendError('Error al renovar el token', [], 500);
        }
    }


    public function me()
    {
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return $this->sendError('Usuario no encontrado', [], 404);
            }

            // Obtener los roles y permisos del usuario
            // $roles = $user->getRoleNames()->first();
            $roles = $user->getRoleNames();
            $permissions = $user->getPermissionsViaRoles();

            return $this->sendResponse('Usuario recuperado exitosamente', $user);
        } catch (\Exception $e) {
            return $this->sendError('Error al recuperar el usuario', $e->getMessage(), 500);
        }
    }


    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());

            return $this->sendResponse('Cierre de sesión exitoso');
        } catch (JWTException $e) {
            return $this->sendError('Fallo en el cierre de sesión', $e->getMessage(), 500);
        }
    }
}

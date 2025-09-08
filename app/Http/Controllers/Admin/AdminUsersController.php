<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use App\Models\User;
use App\Enums\RolesEnum;
use App\Enums\TarifasEnum;
use App\Enums\AmbientesEnum;

class AdminUsersController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            // Verificación de permisos
            new Middleware('role_or_permission:view users', only: ['index', 'show']),
            new Middleware('permission:create users', only: ['store']),
            new Middleware('permission:edit users', only: ['update']),
            new Middleware('permission:delete users', only: ['destroy']),
        ];
    }


    /* ----------------------------- Listar usuarios ---------------------------- */
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 10);
            $users = User::role(RolesEnum::ADMIN)
                ->where('id', '!=', auth()->user()->id)
                ->paginate($perPage);

            return $this->sendResponse('Usuarios recuperados exitosamente', $users);
        } catch(\Exception $e) {
            return $this->sendError('No se pudo recuperar la lista de usuarios', $e->getMessage(), 500);
        }
    }


    /* ----------------------------- Mostrar un usuario ---------------------------- */
    public function show(User $user)
    {
        try {
            // 1. Verificar si el usuario es el mismo
            if (auth()->user()->id === $user->id) {
                return $this->sendError('No puedes realizar esta acción sobre ti mismo', [], 403);
            }

            // 2. Verificar si el usuario es admin
            if (!$user->hasRole(RolesEnum::ADMIN)) {
                return $this->sendError('Usuario no encontrado o no es administrador', [], 404);
            }

            // 3. Devolver la respuesta
            return $this->sendResponse('Usuario recuperado exitosamente', new UserResource($user));
        } catch(\Exception $e) {
            return $this->sendError('No se pudo recuperar el usuario', $e->getMessage(), 500);
        }
    }


    /* ------------------------- Crear un nuevo usuario. ------------------------ */
    public function store(Request $request)
    {
        try {
            // 1. Validar los datos
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'email' => 'required|email|unique:users',
                'tarifa' => 'in:' . implode(',', TarifasEnum::values()),
                'ambiente' => 'required|in:' . implode(',', AmbientesEnum::values()),
                'ruc' => 'string|regex:/^[0-9]{13}$/|unique:users,ruc',
                'razonSocial' => 'string',
                'nombreComercial' => 'string',
                'dirMatriz' => 'string',
                'contribuyenteEspecial' => 'string|nullable',
                'obligadoContabilidad' => 'boolean',
                'password' => 'required|string|min:8',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Datos no validos', $validator->errors(), 400);
            }

            $validated = $validator->validated();

            // 2. Cifrar la contraseña
            $validated['password'] = bcrypt($validated['password']);

            // 3. Crear el usuario
            $user = User::create($validated);

            // 4. Asignar el rol
            $user->assignRole(RolesEnum::ADMIN);

            // 5. Devolver la respuesta
            return $this->sendResponse('Usuario creado exitosamente', new UserResource($user), 201);
        } catch (\Exception $e) {
            return $this->sendError('No se pudo crear el usuario', $e->getMessage(), 500);
        }
    }


    /* ------------------------- Editar un usuario. ------------------------ */
    public function update(Request $request, User $user)
    {
        try {
            // 1. Verificar que el usuario no sea el mismo autorizado
            if ($user->id === auth()->id()) {
                return $this->sendError('No puedes realizar esta acción sobre ti mismo', [], 403);
            }

            // 2. Verificar que el usuario tenga el rol 'admin'
            if (!$user->hasRole(RolesEnum::ADMIN)) {
                return $this->sendError('Usuario no encontrado o no es administrador', [], 404);
            }

            // 3. Validar los datos proporcionados
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string',
                'email' => 'sometimes|email|unique:users,email,' . $user->id,
                'tarifa' => 'sometimes|in:' . implode(',', TarifasEnum::values()),
                'ambiente' => 'sometimes|in:' . implode(',', AmbientesEnum::values()),
                'ruc' => 'sometimes|string|regex:/^[0-9]{13}$/|unique:users,ruc,' . $user->id,
                'razonSocial' => 'sometimes|string',
                'nombreComercial' => 'sometimes|string',
                'dirMatriz' => 'sometimes|string',
                'contribuyenteEspecial' => 'sometimes|string|nullable',
                'obligadoContabilidad' => 'sometimes|boolean',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Datos no validos', $validator->errors(), 400);
            }

            $validated = $validator->validated();

            // 4. Actualizar el usuario
            $user->update($validated);

            // 5. Retornar el recurso actualizado
            return $this->sendResponse('Usuario actualizado exitosamente', new UserResource($user));
        } catch (\Exception $e) {
            return $this->sendError('No se pudo actualizar el usuario', $e->getMessage(), 500);
        }
    }


    /* ------------------------- Eliminar un usuario. ------------------------ */
    public function destroy(User $user)
    {
        try {
            // 1. Verificar que el usuario no sea el mismo autorizado
            if ($user->id === auth()->id()) {
                return $this->sendError('No puedes realizar esta acción sobre ti mismo', [], 403);
            }

            // 2. Verificar que el usuario tenga el rol 'admin'
            if (!$user->hasRole(RolesEnum::ADMIN)) {
                return $this->sendError('Usuario no encontrado o no es administrador', [], 404);
            }

            // 3. Eliminar el usuario
            $user->delete();

            // 4. Devolver la respuesta
            return $this->sendResponse('Usuario eliminado exitosamente', [], 200);
        } catch (\Exception $e) {
            return $this->sendError('No se pudo eliminar el usuario', $e->getMessage(), 500);
        }
    }
}

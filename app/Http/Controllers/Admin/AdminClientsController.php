<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoadSignatureRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Controllers\HasMiddleware;
use App\Models\User;
use App\Enums\RolesEnum;
use App\Enums\TarifasEnum;
use App\Enums\AmbientesEnum;
use App\Services\CertificadoFirma;

class AdminClientsController extends Controller implements HasMiddleware
{
    protected $certificadoFirmaService;

    public function __construct(CertificadoFirma $certificadoFirmaService)
    {
        $this->certificadoFirmaService = $certificadoFirmaService;
    }


    public static function middleware(): array
    {
        return [
            // Verificación de permisos
            // new Middleware('role_or_permission:view clients', only: ['index', 'show']),
            // new Middleware('permission:create clients', only: ['store']),
            // new Middleware('permission:edit clients', only: ['update']),
            // new Middleware('permission:delete clients', only: ['destroy']),
            // new Middleware('permission:load signature', only: ['load_signature']),
        ];
    }


    /* ----------------------------- Listar clientes ---------------------------- */
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $clients = User::role(RolesEnum::CLIENTE)
                ->where('id', '!=', auth()->user()->id)
                ->paginate($perPage);

            return $this->sendResponse('Clientes recuperados exitosamente', $clients);
        } catch(\Exception $e) {
            return $this->sendError('No se pudo recuperar la lista de clientes', $e->getMessage(), 500);
        }
    }


    /* ----------------------------- Obtener un cliente ---------------------------- */
    public function show(User $client)
    {
        try {
            // 1. Verificar si el usuario es cliente
            if (!$client->hasRole(RolesEnum::CLIENTE)) {
                return $this->sendError('Cliente no encontrado o no es cliente', [], 404);
            }

            // 2. Devolver la respuesta
            return $this->sendResponse('Cliente recuperado exitosamente', new UserResource($client));
        } catch(\Exception $e) {
            return $this->sendError('No se pudo recuperar el cliente', $e->getMessage(), 500);
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
                'tarifa' => 'required|in:' . implode(',', TarifasEnum::values()),
                'ambiente' => 'required|in:' . implode(',', AmbientesEnum::values()),
                'ruc' => 'required|string|regex:/^[0-9]{13}$/|unique:users,ruc',
                'razonSocial' => 'required|string',
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

            // 3. Crear el cliente
            $client = User::create($validated);

            // 4. Asignar el rol
            $client->assignRole(RolesEnum::CLIENTE);

            // 5. Devolver la respuesta
            return $this->sendResponse('Cliente creado exitosamente', new UserResource($client), 201);
        } catch (\Exception $e) {
            return $this->sendError('No se pudo crear el cliente', $e->getMessage(), 500);
        }
    }


    /* ------------------------- Actualizar un cliente. ------------------------ */
    public function update(Request $request, User $client)
    {
        try {
            // 1. Verificar que el usuario tenga el rol 'CLIENTE'
            if (!$client->hasRole(RolesEnum::CLIENTE)) {
                return $this->sendError('Usuario no encontrado o no es cliente', [], 404);
            }

            // 2. Validar los datos proporcionados
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string',
                'email' => 'sometimes|email|unique:users,email,' . $client->id,
                'tarifa' => 'sometimes|in:' . implode(',', TarifasEnum::values()),
                'ambiente' => 'sometimes|in:' . implode(',', AmbientesEnum::values()),
                'ruc' => 'sometimes|string|regex:/^[0-9]{13}$/|unique:users,ruc,' . $client->id,
                'razonSocial' => 'sometimes|string',
                'nombreComercial' => 'sometimes|string',
                'dirMatriz' => 'sometimes|string',
                'contribuyenteEspecial' => 'sometimes|string|nullable',
                'obligadoContabilidad' => 'sometimes|boolean',
                'active_account' => 'sometimes|boolean',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Datos no validos', $validator->errors(), 400);
            }

            $validated = $validator->validated();

            // 3. Actualizar el cliente
            $client->update($validated);

            // 5. Retornar el recurso actualizado
            return $this->sendResponse('Cliente actualizado exitosamente', new UserResource($client));
        } catch (\Exception $e) {
            return $this->sendError('No se pudo actualizar el cliente', $e->getMessage(), 500);
        }
    }


    /* ------------------------- Eliminar un cliente. ------------------------ */
    public function destroy(User $client)
    {
        try {
            // 1. Verificar que el usuario tenga el rol 'cliente'
            if (!$client->hasRole(RolesEnum::CLIENTE)) {
                return $this->sendError('Usuario no encontrado o no es cliente', [], 404);
            }

            // 2. Eliminar el cliente
            $client->delete();

            // 3. Devolver la respuesta
            return $this->sendResponse('Cliente eliminado exitosamente', [], 200);
        } catch (\Exception $e) {
            return $this->sendError('No se pudo eliminar el cliente', $e->getMessage(), 500);
        }
    }


    // --------------- Cargar o actualizar el certificado y firma --------------- */
    public function load_signature(LoadSignatureRequest $request, User $client)
    {
        try {
            // 1. Verificar que el usuario tenga el rol 'cliente'
            if (!$client->hasRole(RolesEnum::CLIENTE)) {
                return $this->sendError('Usuario no encontrado o no es cliente', [], 404);
            }

            // 2. Cargar el archivo .p12 y la clave de firma
            $certificateFile = $request->file('signature_file');
            $certificateKey = $request->input('signature_key');
            $existingCertificatePath = $client->signature_path;

            // 3. Gestionar el certificado usando el servicio
            $result = $this->certificadoFirmaService->handleCertificate(
                $certificateFile,
                $certificateKey,
                $client->ruc,
                $existingCertificatePath
            );

            // 4. Actualizar al usuario con la informacion del nuevo certificado
            $client->update([
                'signature_path' => $result['signature_path'],
                'signature_key' => $result['signature_key'],
                'signature_expires_at' => $result['expires_at'],
            ]);

            return $this->sendResponse('Certificado y clave de firma cargados exitosamente', [
                'expires_at' => $result['expires_at'],
            ]);
        } catch(\Exception $e) {
            return $this->sendError('No se pudo cargar el certificado', $e->getMessage(), 500);
        }
    }
}

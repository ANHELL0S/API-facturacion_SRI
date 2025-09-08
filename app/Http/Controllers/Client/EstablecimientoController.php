<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Establecimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Auth\Access\AuthorizationException;

class EstablecimientoController extends Controller
{
    public function index()
    {
        try {
            $perPage = request()->query('per_page', 10);

            $userId = auth()->id();
            $establecimientos = Establecimiento::where('user_id', $userId)->paginate($perPage);

            return $this->sendResponse('Establecimientos recuperados con éxito', $establecimientos);
        } catch (\Exception $e) {
            return $this->sendError('Error al recuperar los establecimientos', $e->getMessage(), 500);
        }
    }


    public function store(Request $request)
    {
        try {
            $validator = \Validator::make($request->all(), [
                'nombre' => 'required|string|max:255',
                'direccion' => 'required|string|max:100',
                'numero' => [
                    'nullable',
                    'regex:/^\d{3}$/',
                    'not_in:000',
                    Rule::unique('establecimientos')->where(function ($query) {
                        $query->where('user_id', auth()->id());
                    })
                ]
            ], [
                'numero.regex' => 'El número debe ser un valor de tres dígitos.',
                'numero.not_in' => 'El número no puede ser 000.',
                'numero.unique' => 'El número ya está en uso.',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Datos no válidos', $validator->errors(), 422);
            }

            $validated_data = $validator->validated();

            // Verificar si el número fue proporcionado
            $numero = $request->numero;
            if ($validated_data['numero'] === null) {
                // Obtener el último número y autoincrementarlo
                $lastEstablecimiento = Establecimiento::orderBy('numero', 'desc')->first();
                if ($lastEstablecimiento) {
                    $lastNumero = intval($lastEstablecimiento->numero);
                    $numero = str_pad($lastNumero + 1, 3, '0', STR_PAD_LEFT);
                } else {
                    $numero = '001';
                }
            }

            $establecimiento = new Establecimiento();
            $establecimiento->nombre = $request->nombre;
            $establecimiento->numero = $numero;
            $establecimiento->direccion = $request->direccion;
            $establecimiento->user_id = auth()->id();
            $establecimiento->save();

            return $this->sendResponse('Establecimiento creado exitosamente', $establecimiento, 201);
        } catch (\Exception $e) {
            return $this->sendError('Error al crear el establecimiento', $e->getMessage(), 500);
        }
    }


    public function show(Establecimiento $establecimiento)
    {
        try {
            Gate::authorize('view', $establecimiento);

            return $this->sendResponse('Establecimiento recuperado con éxito', $establecimiento);
        } catch (AuthorizationException $e) {
            return $this->sendError('Acceso denegado', $e->getMessage(), 403);
        } catch (\Exception $e) {
            return $this->sendError('Error al recuperar el establecimiento', $e->getMessage(), 500);
        }
    }


    public function update(Request $request, Establecimiento $establecimiento)
    {
        try {
            Gate::authorize('update', $establecimiento);

            // Validar los datos de la solicitud
            $validation = \Validator::make($request->all(), [
                'nombre' => 'nullable|string|max:255',
                'direccion' => 'nullable|string|max:100',
                'numero' => [
                    'nullable',
                    'regex:/^\d{3}$/',
                    'not_in:000',
                    Rule::unique('establecimientos')->where(function ($query) {
                        $query->where('user_id', auth()->id());
                    })->ignore($establecimiento->id)
                ]
            ], [
                'numero.regex' => 'El número debe ser un valor de tres dígitos.',
                'numero.not_in' => 'El número no puede ser 000.',
                'numero.unique' => 'El número ya está en uso.',
            ]);

            if ($validation->fails()) {
                return $this->sendError('Datos no válidos', $validation->errors(), 422);
            }

            $validated_data = $validation->validated();

            $establecimiento->nombre = $validated_data['nombre'] ?? $establecimiento->nombre;
            $establecimiento->numero = $validated_data['numero'] ?? $establecimiento->numero;
            $establecimiento->direccion = $validated_data['direccion'] ?? $establecimiento->direccion;
            $establecimiento->save();

            return $this->sendResponse('Establecimiento actualizado exitosamente', $establecimiento);
        } catch (AuthorizationException $e) {
            return $this->sendError('Acceso denegado', $e->getMessage(), 403);
        } catch (\Exception $e) {
            return $this->sendError('Error al actualizar el establecimiento', $e->getMessage(), 500);
        }
    }


    public function destroy(Establecimiento $establecimiento)
    {
        try {
            Gate::authorize('delete', $establecimiento);

            $establecimiento->delete();

            return $this->sendResponse('Establecimiento eliminado exitosamente');
        } catch (AuthorizationException $e) {
            return $this->sendError('Acceso denegado', $e->getMessage(), 403);
        } catch (\Exception $e) {
            return $this->sendError('Error al eliminar el establecimiento', $e->getMessage(), 500);
        }
    }
}

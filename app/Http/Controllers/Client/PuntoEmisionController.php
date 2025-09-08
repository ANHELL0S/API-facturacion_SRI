<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\PuntoEmision;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use App\Models\Establecimiento;
use Illuminate\Auth\Access\AuthorizationException;


class PuntoEmisionController extends Controller
{
    public function index()
    {
        try {
            $userId = auth()->id();
            $perPage = request()->query('per_page', 10);
            $filters = request()->only(['establecimiento_id', 'estado']);

            $establecimientos = Establecimiento::getEstablecimientosByUserId($userId);

            if ($establecimientos->isEmpty()) {
                return $this->sendError('No se encontraron establecimientos registrados', [], 404);
            }

            $query = PuntoEmision::whereIn('establecimiento_id', $establecimientos->pluck('id'))->with('establecimiento');

            // Aplicar filtros
            if (!empty($filters['establecimiento_id'])) {
                $query->where('establecimiento_id', $filters['establecimiento_id']);
            }
            if (!empty($filters['estado'])) {
                $query->where('estado', $filters['estado']);
            }

            $puntosEmision = $query->paginate($perPage);

            return $this->sendResponse('Puntos de emisión recuperados con éxito', $puntosEmision);
        } catch (\Exception $e) {
            return $this->sendError('Error al recuperar los puntos de emisión', $e->getMessage(), 500);
        }
    }


    public function store(Request $request)
    {
        try {
            $validation = \Validator::make($request->all(), [
                'establecimiento_id' => 'required|exists:establecimientos,id',
                'nombre' => 'required|string|max:100',
                'numero' => 'nullable|regex:/^\d{3}$/|not_in:000',
                'ultimoSecuencial' => 'nullable|regex:/^\d{9}$/',
            ], [
                'establecimiento_id.exists' => 'El establecimiento proporcionado no existe.',
                'numero.regex' => 'El número debe ser un valor de tres dígitos.',
                'numero.not_in' => 'El número no puede ser 000.',
            ]);
            if ($validation->fails()) {
                return $this->sendError('Datos no válidos', $validation->errors(), 422);
            }

            $validated_data = $validation->validated();

            // Validar acceso de creacion de establecimiento
            \Gate::authorize('create', [PuntoEmision::class, $validated_data['establecimiento_id']]);

            // Verificar si el número fue proporcionado
            if ($request->has('numero')) {
                // Verificar si el número ya existe para el establecimiento
                $existingPuntoEmision = PuntoEmision::where('numero', $request->numero)
                    ->where('establecimiento_id', $validated_data['establecimiento_id'])
                    ->first();
                if ($existingPuntoEmision) {
                    return $this->sendError("El número proporcionado ($request->numero) ya existe", [], 400);
                }
                $numero = $request->numero;
            } else {
                // Obtener el último número y autoincrementarlo para el establecimiento
                $lastPuntoEmision = PuntoEmision::where('establecimiento_id', $validated_data['establecimiento_id'])
                    ->orderBy('numero', 'desc')
                    ->first();
                if ($lastPuntoEmision) {
                    $lastNumero = intval($lastPuntoEmision->numero);
                    $numero = str_pad($lastNumero + 1, 3, '0', STR_PAD_LEFT);
                } else {
                    $numero = '001';
                }
            }

            // El secuencial siempre debe empezar en '000000000' para cada nuevo punto de emisión
            $ultimoSecuencial = '000000000';

            // Crear y guardar el nuevo punto de emisión
            $puntoEmision = new PuntoEmision();
            $puntoEmision->establecimiento_id = $validated_data['establecimiento_id'];
            $puntoEmision->nombre = $request->input('nombre');
            $puntoEmision->numero = $numero;
            $puntoEmision->ultimoSecuencial = $ultimoSecuencial;
            $puntoEmision->save();

            return $this->sendResponse('Punto de emisión creado exitosamente', $puntoEmision, 201);
        } catch (AuthorizationException $e) {
            return $this->sendError('Acceso denegado', $e->getMessage(), 403);
        } catch (\Exception $e) {
            return $this->sendError('Error al crear el punto de emisión', $e->getMessage(), 500);
        }
    }


    public function show(PuntoEmision $puntoEmision)
    {
        try {
            // Verificar si el punto de emisión pertenece a un establecimiento del usuario autenticado
            Gate::authorize('view', $puntoEmision);

            return $this->sendResponse('Punto de emisión recuperado con éxito', $puntoEmision);
        } catch (AuthorizationException $e) {
            return $this->sendError('Acceso denegado', $e->getMessage(), 403);
        } catch (\Exception $e) {
            return $this->sendError('Error al recuperar el punto de emisión', $e->getMessage(), 500);
        }
    }


    public function update(Request $request, PuntoEmision $puntoEmision)
    {
        try {
            // Validar acceso de actualización de establecimiento
            \Gate::authorize('update', $puntoEmision);

            // Validar los datos de la solicitud
            $validation = \Validator::make($request->all(), [
                'nombre' => 'required|string|max:100',
                'numero' => 'nullable|regex:/^\d{3}$/|unique:puntos_emision,numero,' . $puntoEmision->id . ',id,establecimiento_id,' . $puntoEmision->establecimiento_id . '|not_in:000',
                'ultimoSecuencial' => 'nullable|regex:/^\d{9}$/',
            ], [
                'numero.regex' => 'El número debe ser un valor de tres dígitos.',
                'numero.not_in' => 'El número no puede ser 000.',
                'numero.unique' => 'El número ya está en uso para este establecimiento.',
            ]);
            if ($validation->fails()) {
                return $this->sendError('Datos no válidos', $validation->errors(), 422);
            }

            $validated_data = $validation->validated();

            // Actualizar ultimo secuencial
            if (isset($validated_data['ultimoSecuencial'])) {
                $puntoEmision->ultimoSecuencial = $validated_data['ultimoSecuencial'];
            }

            // Actualizar los datos del punto de emisión
            $puntoEmision->nombre = $validated_data['nombre'];
            $puntoEmision->save();

            return $this->sendResponse('Punto de emisión actualizado exitosamente', $puntoEmision);
        } catch (AuthorizationException $e) {
            return $this->sendError('Acceso denegado', $e->getMessage(), 403);
        } catch (\Exception $e) {
            return $this->sendError('Error al actualizar el punto de emisión', $e->getMessage(), 500);
        }
    }


    public function updateSecuencial(Request $request, PuntoEmision $punto_emision)
    {
        try {
            Gate::authorize('update', $punto_emision);

            $validator = \Validator::make($request->all(), [
                'proximo_secuencial' => ['required', 'string', 'digits:9']
            ]);

            if ($validator->fails()) {
                return $this->sendError('Datos no válidos', $validator->errors(), 422);
            }

            $punto_emision->proximo_secuencial = $request->proximo_secuencial;
            $punto_emision->save();

            return $this->sendResponse('Secuencial actualizado correctamente.', $punto_emision);

        } catch (AuthorizationException $e) {
            return $this->sendError('Acceso denegado', $e->getMessage(), 403);
        } catch (\Exception $e) {
            return $this->sendError('Error al actualizar el secuencial', $e->getMessage(), 500);
        }
    }


    public function destroy(PuntoEmision $puntoEmision)
    {
        try {
            Gate::authorize('delete', $puntoEmision);

            $puntoEmision->delete();
            return $this->sendResponse('Punto de emisión eliminado exitosamente');
        } catch (AuthorizationException $e) {
            return $this->sendError('Acceso denegado', $e->getMessage(), 403);
        } catch (\Exception $e) {
            return $this->sendError('Error al eliminar el punto de emisión', $e->getMessage(), 500);
        }
    }


    public function reset(PuntoEmision $puntoEmision)
    {
        try {
            Gate::authorize('reset', $puntoEmision);

            $puntoEmision->reset();

            return $this->sendResponse('Punto de emisión reiniciado exitosamente', $puntoEmision);
        } catch (AuthorizationException $e) {
            return $this->sendError('Acceso denegado', $e->getMessage(), 403);
        } catch (\Exception $e) {
            return $this->sendError('Error al reiniciar el punto de emisión', $e->getMessage(), 500);
        }
    }
}

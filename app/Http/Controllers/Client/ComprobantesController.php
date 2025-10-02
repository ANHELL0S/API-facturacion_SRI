<?php

namespace App\Http\Controllers\Client;

use App\Enums\AmbientesEnum;
use App\Enums\EstadosComprobanteEnum;
use App\Enums\TipoComprobanteEnum;
use App\Exceptions\SriException;
use App\Http\Controllers\Controller;
use App\Models\Comprobante;
use App\Models\PuntoEmision;
use App\Http\Requests\FacturaRequest;
use App\Jobs\ProcessBulkDownloadChunkJob;
use App\Jobs\CreateFinalZipJob;
use App\Models\BulkDownloadJob;
use App\Services\ClaveAccesoBarcode;
use Illuminate\Support\Facades\Bus;
use App\Services\FileGenerationService;
use App\Services\SincronoComprobanteService;
use App\Services\SriComprobanteService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PDF;
use ZipArchive;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


class ComprobantesController extends Controller
{
    protected $sriService;
    protected $fileGenerationService;

    public function __construct(SriComprobanteService $sriService, FileGenerationService $fileGenerationService)
    {
        $this->sriService = $sriService;
        $this->fileGenerationService = $fileGenerationService;
    }

    private function validarClaveAcceso(string $claveAcceso)
    {
        $validator = \Validator::make(['clave_acceso' => $claveAcceso], [
            'clave_acceso' => ['required', 'string', 'size:49', 'regex:/^[0-9]+$/']
        ]);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }
    }

    public function index(Request $request)
    {
        // Validar los filtros
        $validator = \Validator::make($request->all(), [
            'tipo' => ['nullable', 'string', 'in:' . implode(',', TipoComprobanteEnum::values())],
            'estado' => ['nullable', 'string', 'in:' . implode(',', EstadosComprobanteEnum::values())],
            'ambiente' => ['nullable', 'string', 'in:' . implode(',', AmbientesEnum::values())],
            'fecha_desde' => ['nullable', 'date'],
            'fecha_hasta' => ['nullable', 'date', 'after_or_equal:fecha_desde'],
        ]);

        if ($validator->fails()) {
            return $this->sendError(
                'Parámetros no válidos',
                $validator->errors(),
                422
            );
        }

        try {
            $user = auth()->user();

            $query = Comprobante::where('user_id', $user->id);

            if ($request->filled('tipo')) {
                $query->where('tipo_comprobante', $request->tipo);
            }

            if ($request->filled('ambiente')) {
                $query->where('ambiente', $request->ambiente);
            }

            if ($request->filled('estado')) {
                $query->where('estado', $request->estado);
            }

            if ($request->filled('fecha_desde')) {
                $query->whereDate('fecha_emision', '>=', $request->fecha_desde);
            }

            if ($request->filled('fecha_hasta')) {
                $query->whereDate('fecha_emision', '<=', $request->fecha_hasta);
            }

            $comprobantes = $query->orderByDesc('fecha_emision')->get();

            return $this->sendResponse(
                'Comprobantes recuperados exitosamente.',
                $comprobantes,
                200
            );
        } catch (AuthorizationException $e) {
            return $this->sendError('Acceso denegado', $e->getMessage(), 403);
        } catch (\Exception $e) {
            return $this->sendError('Error al obtener los comprobantes', $e->getMessage(), 500);
        }
    }

    public function exportAuthorized(Request $request)
    {
        try {
            $validator = \Validator::make($request->all(), [
                'fecha_desde' => ['nullable', 'date'],
                'fecha_hasta' => ['nullable', 'date', 'after_or_equal:fecha_desde'],
            ]);

            if ($validator->fails()) {
                return $this->sendError(
                    'Parámetros no válidos',
                    $validator->errors(),
                    422
                );
            }

            $user = auth()->user();
            $query = Comprobante::where('user_id', $user->id)->where('estado', 'autorizado');

            if ($request->filled('fecha_desde') && $request->filled('fecha_hasta')) {
                $fecha_desde = Carbon::parse($request->fecha_desde)->startOfDay();
                $fecha_hasta = Carbon::parse($request->fecha_hasta)->endOfDay();
                $query->whereBetween('fecha_autorizacion', [$fecha_desde, $fecha_hasta]);
            }

            $comprobantes = $query->orderByDesc('fecha_emision')->get();

            return $this->sendResponse(
                'Comprobantes autorizados recuperados exitosamente para exportación.',
                $comprobantes,
                200
            );
        } catch (\Exception $e) {
            return $this->sendError('Error al obtener los comprobantes para exportación', $e->getMessage(), 500);
        }
    }

    public function export(Request $request)
    {
        // Validar los filtros
        $validator = \Validator::make($request->all(), [
            'tipo' => ['nullable', 'string', 'in:' . implode(',', TipoComprobanteEnum::values())],
            'estado' => ['nullable', 'string', 'in:' . implode(',', EstadosComprobanteEnum::values())],
            'ambiente' => ['nullable', 'string', 'in:' . implode(',', AmbientesEnum::values())],
            'fecha_desde' => ['nullable', 'date'],
            'fecha_hasta' => ['nullable', 'date', 'after_or_equal:fecha_desde'],
        ]);

        if ($validator->fails()) {
            return $this->sendError(
                'Parámetros no válidos',
                $validator->errors(),
                422
            );
        }

        try {
            $user = auth()->user();

            $query = Comprobante::where('user_id', $user->id);

            if ($request->filled('tipo')) {
                $query->where('tipo_comprobante', $request->tipo);
            }

            if ($request->filled('ambiente')) {
                $query->where('ambiente', $request->ambiente);
            }



            if ($request->filled('estado')) {
                $query->where('estado', $request->estado);
            }

            if ($request->filled('fecha_desde')) {
                $query->whereDate('fecha_emision', '>=', $request->fecha_desde);
            }

            if ($request->filled('fecha_hasta')) {
                $query->whereDate('fecha_emision', '<=', $request->fecha_hasta);
            }

            $comprobantes = $query->orderByDesc('fecha_emision')->get();

            return $this->sendResponse(
                'Comprobantes recuperados exitosamente para exportación.',
                $comprobantes,
                200
            );
        } catch (AuthorizationException $e) {
            return $this->sendError('Acceso denegado', $e->getMessage(), 403);
        } catch (\Exception $e) {
            return $this->sendError('Error al obtener los comprobantes para exportación', $e->getMessage(), 500);
        }
    }


    public function show(string $clave_acceso)
    {
        try {
            $this->validarClaveAcceso($clave_acceso);

            $comprobante = Comprobante::findByClaveAcceso($clave_acceso);

            Gate::authorize('view', $comprobante);

            return $this->sendResponse(
                'Comprobante recuperado correctamente.',
                $comprobante,
                200
            );
        } catch (AuthorizationException $e) {
            return $this->sendError('Acceso denegado', $e->getMessage(), 403);
        } catch (ModelNotFoundException $e) {
            return $this->sendError('Comprobante no encontrado', 'No se encontró el comprobante con la clave de acceso proporcionada.', 404);
        } catch (\Exception $e) {
            return $this->sendError('Error al recuperar el comprobante', $e->getMessage(), 404);
        }
    }


    public function getEstado(string $clave_acceso)
    {
        try {
            $this->validarClaveAcceso($clave_acceso);

            $comprobante = Comprobante::findByClaveAcceso($clave_acceso);

            Gate::authorize('view', $comprobante);

            $estado = $comprobante->estado;

            return $this->sendResponse(
                'Estado del comprobante obtenido correctamente.',
                ['estado' => $estado],
                200
            );
        } catch (AuthorizationException $e) {
            return $this->sendError('Acceso denegado', $e->getMessage(), 403);
        } catch (ModelNotFoundException $e) {
            return $this->sendError('Comprobante no encontrado', 'No se encontró el comprobante con la clave de acceso proporcionada.', 404);
        } catch (\Exception $e) {
            return $this->sendError('Error al obtener el estado del comprobante', [], 500);
        }
    }


    public function getXml(string $clave_acceso)
    {
        try {
            $this->validarClaveAcceso($clave_acceso);
            $comprobante = Comprobante::findByClaveAcceso($clave_acceso);
            $xml = $this->fileGenerationService->generateXmlContent($comprobante);

            return $this->sendResponse(
                'XML obtenido exitosamente',
                ['xml' => $xml]
            );
        } catch (AuthorizationException $e) {
            return $this->sendError('Acceso denegado', $e->getMessage() . $e->getTrace(), 403);
        } catch (ModelNotFoundException $e) {
            return $this->sendError('Comprobante no encontrado', 'No se encontró el comprobante con la clave de acceso proporcionada.', 404);
        } catch (SriException $e) {
            return $this->sendError('Error de consulta en el SRI', $e->getMessage(), 409);
        } catch (\Exception $e) {
            return $this->sendError('Error inesperado al consultar el XML', $e->getMessage(), 500);
        }
    }

    public function getPdf(string $clave_acceso)
    {
        try {
            $this->validarClaveAcceso($clave_acceso);
            $comprobante = Comprobante::findByClaveAcceso($clave_acceso);
            $fileName = '';
            $pdfContent = $this->fileGenerationService->generatePdfContent($comprobante, $fileName);

            return response($pdfContent)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');

        } catch (AuthorizationException $e) {
            return $this->sendError('Acceso denegado', $e->getMessage(), 403);
        } catch (ModelNotFoundException $e) {
            return $this->sendError('Comprobante no encontrado', 'No se encontró el comprobante con la clave de acceso proporcionada.', 404);
        } catch (SriException $e) {
            return $this->sendError('Error de consulta en el SRI', $e->getMessage(), 409);
        } catch (\Exception $e) {
            return $this->sendError('Error inesperado al generar el PDF', $e->getMessage(), 500);
        }
    }

    public function descargarMasivo(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'claves_acceso' => ['nullable', 'array'],
            'claves_acceso.*' => ['string', 'size:49', 'regex:/^[0-9]+$/'],
            'format' => ['required', 'string', 'in:pdf,xml'],
            'fecha_desde' => ['nullable', 'date'],
            'fecha_hasta' => ['nullable', 'date', 'after_or_equal:fecha_desde'],
            'product_code' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Parámetros no válidos', $validator->errors(), 422);
        }

        $user = Auth::user();
        $format = $request->input('format');
        $clavesAcceso = [];

        if ($request->has('claves_acceso')) {
            $clavesAcceso = $request->input('claves_acceso');
        } else {
            // Start with a fresh query, select distinct comprobantes to handle multiple products per invoice
            $query = Comprobante::query()->select('comprobantes.*')->distinct();

            // Base conditions with qualified column names
            $query->where('comprobantes.user_id', $user->id);
            $query->where('comprobantes.estado', 'autorizado');

            if ($request->filled('fecha_desde')) {
                $query->whereDate('comprobantes.fecha_emision', '>=', $request->fecha_desde);
            }

            if ($request->filled('fecha_hasta')) {
                $query->whereDate('comprobantes.fecha_emision', '<=', $request->fecha_hasta);
            }

            $comprobantes = $query->get();

            if ($request->filled('product_code')) {
                $productCode = $request->product_code;

                // Filter the collection in PHP memory to ensure the logic is identical to what the user sees elsewhere.
                // This avoids database-specific JSON query issues.
                $comprobantes = $comprobantes->filter(function ($comprobante) use ($productCode) {
                    $payload = $comprobante->payload;

                    // Handle cases where the payload might be a double-encoded JSON string.
                    if (is_string($payload)) {
                        $payload = json_decode($payload, true);
                    }

                    if (!isset($payload['detalles']) || !is_array($payload['detalles'])) {
                        return false;
                    }

                    foreach ($payload['detalles'] as $detalle) {
                        // Use a loose comparison to handle "123" == 123
                        if (isset($detalle['codigoPrincipal']) && $detalle['codigoPrincipal'] == $productCode) {
                            return true;
                        }
                    }

                    return false;
                });
            }

            $clavesAcceso = $comprobantes->pluck('clave_acceso')->toArray();
        }

        if (empty($clavesAcceso)) {
            return $this->sendError('No se encontraron comprobantes', 'No hay comprobantes que coincidan con los filtros seleccionados.', 404);
        }

        $job = BulkDownloadJob::create([
            'user_id' => $user->id,
            'format' => $format,
            'total_files' => count($clavesAcceso),
        ]);

        $chunks = array_chunk($clavesAcceso, 200); // Process 200 invoices per job
        $batchJobs = [];
        foreach ($chunks as $chunk) {
            $batchJobs[] = new ProcessBulkDownloadChunkJob($job, $chunk);
        }

        $batch = Bus::batch($batchJobs)
            ->then(function () use ($job) {
                // All jobs completed successfully...
                CreateFinalZipJob::dispatch($job);
            })
            ->catch(function () use ($job) {
                // A job in the batch failed...
                $job->update(['status' => \App\Enums\BulkDownloadStatusEnum::FAILED]);
            })
            ->finally(function () use ($job) {
                // The batch has finished executing...
            })
            ->name('Bulk Download Job ID: ' . $job->id)
            ->dispatch();

        // Add batch_id to the job model so the frontend can optionally use it.
        // Note: This is not persisted to the database.
        $job->batch_id = $batch->id;

        return $this->sendResponse(
            'La solicitud de descarga ha sido aceptada y se está procesando en segundo plano.',
            $job, // Return the full job object
            202
        );
    }

    public function getBulkDownloadStatus(string $jobId)
    {
        try {
            $job = BulkDownloadJob::findOrFail($jobId);
            Gate::authorize('view', $job);

            return $this->sendResponse('Estado del trabajo de descarga masiva.', $job);
        } catch (ModelNotFoundException $e) {
            return $this->sendError('Trabajo no encontrado', 'No se encontró el trabajo de descarga masiva.', 404);
        } catch (AuthorizationException $e) {
            return $this->sendError('Acceso denegado', 'No tienes permiso para ver este trabajo.', 403);
        }
    }

    public function downloadBulkZip(string $jobId)
    {
        try {
            $job = BulkDownloadJob::findOrFail($jobId);
            Gate::authorize('view', $job);

            if ($job->status !== \App\Enums\BulkDownloadStatusEnum::COMPLETED) {
                return $this->sendError('El archivo no está listo', 'El archivo ZIP aún no está listo para descargar.', 409);
            }

            if (!$job->file_path || !Storage::disk('public')->exists($job->file_path)) {
                $expectedPath = $job->file_path ? Storage::disk('public')->path($job->file_path) : 'null';
                return $this->sendError(
                    'Archivo no encontrado',
                    'El archivo ZIP no se encontró en el servidor. Path: ' . $expectedPath,
                    404
                );
            }

            return Storage::disk('public')->download($job->file_path);
        } catch (ModelNotFoundException $e) {
            return $this->sendError('Trabajo no encontrado', 'No se encontró el trabajo de descarga masiva.', 404);
        } catch (AuthorizationException $e) {
            return $this->sendError('Acceso denegado', 'No tienes permiso para descargar este archivo.', 403);
        }
    }


    public function getProductCodes()
    {
        $user = Auth::user();
        $comprobantes = Comprobante::where('user_id', $user->id)
            ->where('estado', 'autorizado')
            ->get();

        $productCodes = [];
        foreach ($comprobantes as $comprobante) {
            $payload = $comprobante->payload;
            if (is_string($payload)) {
                $payload = json_decode($payload, true);
            }

            if (isset($payload['detalles']) && is_array($payload['detalles'])) {
                foreach ($payload['detalles'] as $detalle) {
                    if (isset($detalle['codigoPrincipal'])) {
                        $productCodes[] = $detalle['codigoPrincipal'];
                    }
                }
            }
        }

        $uniqueProductCodes = array_values(array_unique($productCodes));

        return $this->sendResponse('Códigos de producto recuperados exitosamente.', $uniqueProductCodes);
    }

    public function generateFactura(FacturaRequest $request, PuntoEmision $puntoEmision, SincronoComprobanteService $sincronoService)
    {
        try {
            // 1. Autorizar acceso a punto de emision de usuario
            Gate::authorize('view', $puntoEmision);

            // 2. Validar firma del usuario
            $user = \Auth::user();
            Gate::authorize('firma', $user);

            // 3. Validar datos del comprobante
            $validated_data = $request->validated();

            // Siempre usar la fecha y hora del servidor para asegurar la precisión.
            $validated_data['fechaEmision'] = now()->format('Y-m-d H:i:s');

            // 4. Procesar comprobante de forma síncrona
            $comprobante = $sincronoService->procesarComprobante(
                $validated_data,
                $user,
                $puntoEmision,
                TipoComprobanteEnum::FACTURA
            );

            return $this->sendResponse(
                'Factura generada y autorizada exitosamente.',
                $comprobante,
                201
            );

        } catch (AuthorizationException $e) {
            return $this->sendError('Acceso denegado', $e->getMessage(), $e->status());
        } catch (SriException $e) {
            return $this->sendError(
                'Error del SRI',
                ['sri_error' => $e->getMessage()],
                422 // Unprocessable Entity
            );
        } catch (\Exception $e) {
            return $this->sendError('Error al generar la factura', $e->getMessage(), 500);
        }
    }

    public function getPersona($id)
    {
        try {
            $token = config('services.personas.token');
            $baseUrl = config('services.personas.base_url');

            $isRuc = strlen($id) == 13;
            $url = $baseUrl . ($isRuc ? "api/ruc/" . $id : "api/ci/" . $id);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'User-agent' => 'Laravel',
            ])->get($url);

            if ($response->successful()) {
                $apiData = $response->json();

                if ($isRuc && isset($apiData['data'])) {
                    // For RUC lookups, the API provides 'name'. The frontend expects 'full_name'.
                    $apiData['data']['full_name'] = $apiData['data']['name'] ?? '';
                }
                // For CI lookups, the API already provides 'full_name', so no changes are needed.

                return $this->sendResponse('Datos de persona recuperados exitosamente.', $apiData);
            } else {
                Log::warning('Solicitud a API Personas fallida: ' . $response->status(), ['id' => $id, 'response' => $response->body()]);
                return $this->sendError('No se pudo obtener los datos de la persona.', [], $response->status());
            }
        } catch (\Exception $e) {
            Log::error('Excepción en solicitud a API Personas: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString(), ['id' => $id]);
            return $this->sendError('Ocurrió un error al consultar el servicio de personas.', [], 500);
        }
    }
}
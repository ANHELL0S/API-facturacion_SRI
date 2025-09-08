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
use App\Jobs\CreateBulkDownloadZipJob;
use App\Jobs\GenerarComprobanteJob;
use App\Models\BulkDownloadJob;
use App\Services\AccessKeyGenerator;
use App\Services\ClaveAccesoBarcode;
use App\Services\FileGenerationService;
use App\Services\SriComprobanteService;
use App\Services\GeneradorClaveAcceso;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PDF;
use ZipArchive;
use Carbon\Carbon;

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
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
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

            $perPage = $request->input('per_page', 10);

            $comprobantes = $query->orderByDesc('fecha_emision')->paginate($perPage);

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


    /**
     * Obtener un comprobante por su UUID
     */
    public function showById(string $id)
    {
        try {
            // Validar que el ID sea un UUID válido
            $validator = \Validator::make(['id' => $id], [
                'id' => ['required', 'uuid']
            ]);

            if ($validator->fails()) {
                return $this->sendError(
                    'ID no válido',
                    $validator->errors(),
                    422
                );
            }

            // Buscar el comprobante por UUID
            $comprobante = Comprobante::findOrFail($id);

            // Autorizar que el usuario pueda ver este comprobante
            Gate::authorize('view', $comprobante);

            return $this->sendResponse(
                'Comprobante recuperado correctamente.',
                $comprobante,
                200
            );
        } catch (AuthorizationException $e) {
            return $this->sendError('Acceso denegado', $e->getMessage(), 403);
        } catch (ModelNotFoundException $e) {
            return $this->sendError('Comprobante no encontrado', 'No se encontró el comprobante con el ID proporcionado.', 404);
        } catch (\Exception $e) {
            return $this->sendError('Error al recuperar el comprobante', $e->getMessage(), 500);
        }
    }

    /**
     * Obtener XML de un comprobante por su UUID
     */
    public function getXmlById(string $id)
    {
        try {
            $validator = \Validator::make(['id' => $id], [
                'id' => ['required', 'uuid']
            ]);

            if ($validator->fails()) {
                return $this->sendError('ID no válido', $validator->errors(), 422);
            }

            $comprobante = Comprobante::findOrFail($id);
            Gate::authorize('view', $comprobante);

            $xml = $this->fileGenerationService->generateXmlContent($comprobante);

            return $this->sendResponse(
                'XML obtenido exitosamente',
                ['xml' => $xml]
            );
        } catch (AuthorizationException $e) {
            return $this->sendError('Acceso denegado', $e->getMessage(), 403);
        } catch (ModelNotFoundException $e) {
            return $this->sendError('Comprobante no encontrado', 'No se encontró el comprobante con el ID proporcionado.', 404);
        } catch (\Exception $e) {
            return $this->sendError('Error al obtener el XML', $e->getMessage(), 500);
        }
    }

    /**
     * Obtener PDF de un comprobante por su UUID
     */
    public function getPdfById(string $id)
    {
        try {
            $validator = \Validator::make(['id' => $id], [
                'id' => ['required', 'uuid']
            ]);

            if ($validator->fails()) {
                return $this->sendError('ID no válido', $validator->errors(), 422);
            }

            $comprobante = Comprobante::findOrFail($id);
            Gate::authorize('view', $comprobante);

            $fileName = '';
            $pdfContent = $this->fileGenerationService->generatePdfContent($comprobante, $fileName);

            return response($pdfContent)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
        } catch (AuthorizationException $e) {
            return $this->sendError('Acceso denegado', $e->getMessage(), 403);
        } catch (ModelNotFoundException $e) {
            return $this->sendError('Comprobante no encontrado', 'No se encontró el comprobante con el ID proporcionado.', 404);
        } catch (\Exception $e) {
            return $this->sendError('Error al generar el PDF', $e->getMessage(), 500);
        }
    }

    /**
     * Obtener estado de un comprobante por su UUID
     */
    public function getEstadoById(string $id)
    {
        try {
            // Validar UUID
            $validator = \Validator::make(['id' => $id], [
                'id' => ['required', 'uuid']
            ]);

            if ($validator->fails()) {
                return $this->sendError('ID no válido', $validator->errors(), 422);
            }

            // Buscar comprobante por PK (UUID)
            $comprobante = Comprobante::findOrFail($id); // <-- usar 'id', que es UUID en la tabla

            // Autorización (opcional)
            Gate::authorize('view', $comprobante);

            return $this->sendResponse(
                'Estado del comprobante obtenido correctamente.',
                $comprobante,
                200
            );
        } catch (AuthorizationException $e) {
            return $this->sendError('Acceso denegado', $e->getMessage(), 403);
        } catch (ModelNotFoundException $e) {
            return $this->sendError('Comprobante no encontrado', 'No se encontró el comprobante con el ID proporcionado.', 404);
        } catch (\Exception $e) {
            return $this->sendError('Error al obtener el estado del comprobante', $e->getMessage(), 500);
        }
    }

    /**
     * Re-enviar webhook para un comprobante específico por UUID
     */
    public function resendWebhookById(string $id)
    {
        try {
            $validator = \Validator::make(['id' => $id], [
                'id' => ['required', 'uuid']
            ]);

            if ($validator->fails()) {
                return $this->sendError('ID no válido', $validator->errors(), 422);
            }

            $comprobante = Comprobante::findOrFail($id);
            Gate::authorize('view', $comprobante);

            $payload = json_decode($comprobante->payload, true);
            $saleId = $payload['saleId'] ?? null;
            $callbackUrl = $payload['callbackUrl'] ?? null;

            if (!$saleId || !$callbackUrl) {
                return $this->sendError(
                    'Webhook no configurado',
                    'Este comprobante no tiene configurado webhook callback',
                    404
                );
            }

            // Determinar datos para el callback basado en el estado
            $success = in_array($comprobante->estado, ['autorizado', 'firmado']);
            $data = [
                'estado_sri' => $comprobante->estado,
                'clave_acceso' => $comprobante->clave_acceso,
                'numero_autorizacion' => $comprobante->numero_autorizacion,
                'fecha_autorizacion' => $comprobante->fecha_autorizacion,
            ];

            if (!$success) {
                $data['error_message'] = $comprobante->error_message;
            }

            // Instanciar job para reutilizar la lógica de envío
            $job = new GenerarComprobanteJob(
                $comprobante,
                $comprobante->user,
                $comprobante->puntoEmision,
                TipoComprobanteEnum::from($comprobante->tipo_comprobante)
            );

            $job->enviarCallbackSistemaExterno($success, $data);

            return $this->sendResponse(
                'Webhook re-enviado exitosamente',
                [
                    'comprobante_id' => $comprobante->id,
                    'estado' => $comprobante->estado,
                    'callback_url' => $callbackUrl,
                    'sale_id' => $saleId,
                    'success' => $success
                ]
            );
        } catch (ModelNotFoundException $e) {
            return $this->sendError('Comprobante no encontrado', [], 404);
        } catch (AuthorizationException $e) {
            return $this->sendError('Acceso denegado', $e->getMessage(), 403);
        } catch (\Exception $e) {
            return $this->sendError('Error al re-enviar webhook', $e->getMessage(), 500);
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
            'claves_acceso' => ['required', 'array'],
            'claves_acceso.*' => ['string', 'size:49', 'regex:/^[0-9]+$/'],
            'format' => ['required', 'string', 'in:pdf,xml'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Parámetros no válidos', $validator->errors(), 422);
        }

        $user = Auth::user();
        $clavesAcceso = $request->input('claves_acceso');
        $format = $request->input('format');

        $job = BulkDownloadJob::create([
            'user_id' => $user->id,
            'format' => $format,
            'total_files' => count($clavesAcceso),
        ]);

        CreateBulkDownloadZipJob::dispatch($job, $clavesAcceso);

        return $this->sendResponse('La solicitud de descarga ha sido aceptada y se está procesando en segundo plano.', ['job_id' => $job->id], 202);
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

    public function generateFactura(FacturaRequest $request, PuntoEmision $puntoEmision)
    {
        $validated_data = null;
        try {
            // 1. Autorizar acceso a punto de emision de usuario
            Gate::authorize('view', $puntoEmision);

            // 2. Validar firma del usuario
            $user = \Auth::user();
            Gate::authorize('firma', $user);

            // 3. Validar datos del comprobante
            $validated_data = $request->validated();

            // Si no se proporciona fecha de emisión, usar la fecha actual del servidor
            if (!isset($validated_data['fechaEmision'])) {
                $validated_data['fechaEmision'] = now()->format('Y-m-d');
            }

            // 🔥 USAR EL NUEVO SERVICIO AccessKeyGenerator (estático)
            $claveAcceso = AccessKeyGenerator::generate([
                'fechaEmision' => $validated_data['fechaEmision'],
                'codDoc' => TipoComprobanteEnum::FACTURA->value,
                'ruc' => $user->ruc,
                'ambiente' => $user->ambiente,
                'estab' => $puntoEmision->establecimiento->numero,
                'ptoEmi' => $puntoEmision->numero,
                'secuencial' => $puntoEmision->proximo_secuencial,
            ]);

            // Validar webhook callback
            $saleId = $request->input('saleId');
            $callbackUrl = $request->input('callbackUrl');

            if (($saleId && !$callbackUrl) || (!$saleId && $callbackUrl)) {
                return $this->sendError(
                    'Parámetros incompletos',
                    'Si proporciona saleId o callbackUrl, ambos deben estar presentes',
                    422
                );
            }

            if ($callbackUrl) {
                $urlValidator = \Validator::make(['callbackUrl' => $callbackUrl], [
                    'callbackUrl' => ['required', 'url', 'active_url']
                ]);

                if ($urlValidator->fails()) {
                    return $this->sendError(
                        'URL de callback no válida',
                        $urlValidator->errors(),
                        422
                    );
                }
            }

            // Agregar al payload si están presentes
            if ($saleId && $callbackUrl) {
                $validated_data['saleId'] = $saleId;
                $validated_data['callbackUrl'] = $callbackUrl;
            }

            // 4. Generar comprobante CON LA CLAVE DE ACCESO
            try {
                $comprobante = Comprobante::create([
                    'user_id' => $user->id,
                    'tipo_comprobante' => TipoComprobanteEnum::FACTURA->value,
                    'ambiente' => $user->ambiente,
                    'cliente_email' => $validated_data['infoAdicional']['email'] ?? null,
                    'cliente_ruc' => $validated_data['identificacionComprador'],
                    'fecha_emision' => $validated_data['fechaEmision'],
                    'clave_acceso' => $claveAcceso, // 🔥 GUARDAR CLAVE DE ACCESO
                    'payload' => json_encode($validated_data),
                    'estado' => EstadosComprobanteEnum::PENDIENTE->value,
                ]);
            } catch (\Exception $e) {
                throw new \Exception('Error al registrar el comprobante: ' . $e->getMessage());
            }

            // 5. Lanzar job de generación de comprobante PASANDO LA CLAVE DE ACCESO
            \Log::info("Llamando a job para comprobante: {$comprobante->id} con clave: {$claveAcceso}");
            GenerarComprobanteJob::dispatch(
                $comprobante,
                $user,
                $puntoEmision,
                TipoComprobanteEnum::FACTURA,
                $claveAcceso
            );


            // Respuesta con información completa
            $responseData = [
                'comprobante_id' => $comprobante->id,
                'clave_acceso' => $claveAcceso,
                'estado' => $comprobante->estado,
                'processing' => true,
                'timestamp' => now()->toISOString(),
            ];

            // Incluir info del webhook si se configuró
            if ($saleId && $callbackUrl) {
                $responseData['webhook'] = [
                    'saleId' => $saleId,
                    'callbackUrl' => $callbackUrl,
                    'notificacion' => 'Se enviará notificación al finalizar el procesamiento'
                ];
            }

            return $this->sendResponse(
                'Tu comprobante se está procesando. Recibirás una notificación cuando esté listo',
                $responseData
            );
        } catch (AuthorizationException $e) {
            return $this->sendError('Acceso denegado', $e->getMessage(), $e->status());
        } catch (\Exception $e) {
            return $this->sendError('Error al generar la factura', $e->getMessage(), 500, $validated_data);
        }
    }

    /**
     * Re-enviar webhook para un comprobante específico
     */
    public function resendWebhook(string $comprobanteId)
    {
        try {
            $comprobante = Comprobante::findOrFail($comprobanteId);
            Gate::authorize('view', $comprobante);

            $payload = json_decode($comprobante->payload, true);
            $saleId = $payload['saleId'] ?? null;
            $callbackUrl = $payload['callbackUrl'] ?? null;

            if (!$saleId || !$callbackUrl) {
                return $this->sendError(
                    'Webhook no configurado',
                    'Este comprobante no tiene configurado webhook callback',
                    404
                );
            }

            // Determinar datos para el callback basado en el estado
            $success = in_array($comprobante->estado, ['autorizado', 'firmado']);
            $data = [
                'estado_sri' => $comprobante->estado,
                'clave_acceso' => $comprobante->clave_acceso,
                'numero_autorizacion' => null,
                'fecha_autorizacion' => $comprobante->fecha_autorizacion,
            ];

            if (!$success) {
                $data['error_message'] = $comprobante->error_message;
            }

            // Instanciar job para reutilizar la lógica de envío
            $job = new GenerarComprobanteJob(
                $comprobante,
                $comprobante->user,
                $comprobante->puntoEmision,
                TipoComprobanteEnum::from($comprobante->tipo_comprobante)
            );

            $job->enviarCallbackSistemaExterno($success, $data);

            return $this->sendResponse(
                'Webhook re-enviado exitosamente',
                [
                    'comprobante_id' => $comprobante->id,
                    'estado' => $comprobante->estado,
                    'callback_url' => $callbackUrl,
                    'sale_id' => $saleId,
                    'success' => $success
                ]
            );
        } catch (ModelNotFoundException $e) {
            return $this->sendError('Comprobante no encontrado', [], 404);
        } catch (AuthorizationException $e) {
            return $this->sendError('Acceso denegado', $e->getMessage(), 403);
        } catch (\Exception $e) {
            return $this->sendError('Error al re-enviar webhook', $e->getMessage(), 500);
        }
    }
}

<?php

namespace App\Http\Requests;

use App\Enums\CodigosImpuestosEnum;
use App\Enums\TipoIdentificacionEnum;
use Illuminate\Foundation\Http\FormRequest;

class FacturaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "fechaEmision" => [
                "sometimes",
                "date",
                "date_format:Y-m-d",
                'after_or_equal:' . now()->subDays(3),
                'before_or_equal:' . now(),
            ],
            "tipoIdentificacionComprador" => "required|size:2|in:" . implode(',', TipoIdentificacionEnum::values()),
            "razonSocialComprador" => "required|string|max:300",
            "identificacionComprador" => "required|string|max:20",
            "direccionComprador" => "string|max:300|nullable",

            "totalSinImpuestos" => "required|numeric|min:0|max:1.0E+14",
            "totalDescuento" => "required|numeric|min:0|max:1.0E+14",

            "totalConImpuestos" => "required|array",
            "totalConImpuestos.*.codigo" => "required|numeric|in:" . implode(',', CodigosImpuestosEnum::values()),
            "totalConImpuestos.*.codigoPorcentaje" => "required|numeric|min:0|max:9999",
            "totalConImpuestos.*.baseImponible" => "required|numeric|min:0|max:1.0E+14",
            "totalConImpuestos.*.valor" => "required|numeric|min:0|max:1.0E+14",

            "importeTotal" => "required|numeric|min:0|max:1.0E+14",

            "pagos" => "required|array",
            "pagos.*.formaPago" => "required|string|size:2",
            "pagos.*.total" => "required|numeric|min:0|max:1.0E+14",

            "detalles" => "required|array",
            "detalles.*.codigoPrincipal" => "required|string|max:25",
            "detalles.*.codigoAuxiliar" => "string|max:25",
            "detalles.*.descripcion" => "required|string|max:300",
            "detalles.*.cantidad" => "required|numeric|min:0|regex:/^\d+(\.\d{2,6})?$/",
            "detalles.*.precioUnitario" => "required|numeric|min:0",
            "detalles.*.descuento" => "required|numeric|min:0|max:1.0E+14",
            "detalles.*.precioTotalSinImpuesto" => "required|numeric|min:0|max:1.0E+14",
            "detalles.*.impuestos" => "required|array",
            "detalles.*.impuestos.*.codigo" => "required|integer|in:" . implode(',', CodigosImpuestosEnum::values()),
            "detalles.*.impuestos.*.codigoPorcentaje" => "required|integer|min:0|max:9999",
            "detalles.*.impuestos.*.tarifa" => "required|numeric|min:0|max:9999|regex:/^\d+(\.\d{2})?$/",
            "detalles.*.impuestos.*.baseImponible" => "required|numeric|min:0|max:1.0E+14",
            "detalles.*.impuestos.*.valor" => "required|numeric|min:0|max:1.0E+14",

            "infoAdicional" => "sometimes|array",
            "infoAdicional.*" => "nullable|string|max:300",
            "infoAdicional.telefono" => ['nullable', 'string'],
            "infoAdicional.email" => "nullable|email|max:100",
        ];
    }

    // Este método manejará las validaciones fallidas
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new \Illuminate\Validation\ValidationException($validator, response()->json($validator->errors()));
    }
}

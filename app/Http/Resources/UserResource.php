<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'from_email' => $this->from_email,
            'email_verified_at' => $this->email_verified_at,
            'active_account' => $this->active_account,
            'tarifa' => $this->tarifa,
            'ambiente' => $this->ambiente,
            'ruc' => $this->ruc,
            'razonSocial' => $this->razonSocial,
            'nombreComercial' => $this->nombreComercial,
            'dirMatriz' => $this->dirMatriz,
            'contribuyenteEspecial' => $this->contribuyenteEspecial,
            'obligadoContabilidad' => $this->obligadoContabilidad,
            'enviar_factura_por_correo' => $this->enviar_factura_por_correo,
            'logo_path' => $this->logo_path,
            'signature_expires_at' => $this->signature_expires_at,
            'tipo_impuesto' => $this->tipo_impuesto,
            'codigo_porcentaje_iva' => $this->codigo_porcentaje_iva,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

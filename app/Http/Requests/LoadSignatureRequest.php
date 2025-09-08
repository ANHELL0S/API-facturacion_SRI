<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Archivo p12 formatos aceptados:
                // application/pkcs12
                // application/octet-stream
class LoadSignatureRequest extends FormRequest
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
            'signature_file' => 'required|file|mimetypes:application/pkcs12,application/octet-stream|max:2048', // Máximo 2MB
            'signature_key' => 'required|string',
        ];
    }

    /**
     * Get the custom validation messages for the request's signature fields.
     *
     * @return array<string, string> The validation messages for signature_file and signature_key fields.
     */

    public function messages()
    {
        return [
            'signature_file.required' => 'El archivo de firma es obligatorio.',
            'signature_file.file' => 'El archivo de firma debe ser un archivo válido.',
            'signature_file.mimetypes' => 'El archivo de firma debe ser de tipo .p12.',
            'signature_file.max' => 'El archivo de firma no debe superar los 2MB.',
            'signature_key.required' => 'La clave de firma es obligatoria.',
            'signature_key.string' => 'La clave de firma debe ser un texto válido.',
        ];
    }
}

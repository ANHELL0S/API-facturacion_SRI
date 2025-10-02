<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class AccessKeyGenerator
{
    public static function generate(array $data): string
    {
        try {
            $formattedDate = date('dmY', strtotime($data['fechaEmision']));
            $ruc = $data['ruc'];
            $tipoComprobante = $data['codDoc'];
            $tipoAmbiente = $data['ambiente'];
            $serie = ($data['estab'] ?? '001') . ($data['ptoEmi'] ?? '001');
            $secuencial = str_pad($data['secuencial'], 9, '0', STR_PAD_LEFT);
            $codNumerico = self::generateNumericCode();
            $tipoEmision = '1'; // FIJO

            // Logging lengths of each part
            Log::info('--- Access Key Component Lengths ---');
            Log::info('formattedDate: ' . strlen($formattedDate) . ' (Value: ' . $formattedDate . ')');
            Log::info('tipoComprobante: ' . strlen($tipoComprobante) . ' (Value: ' . $tipoComprobante . ')');
            Log::info('ruc: ' . strlen($ruc) . ' (Value: ' . $ruc . ')');
            Log::info('tipoAmbiente: ' . strlen($tipoAmbiente) . ' (Value: ' . $tipoAmbiente . ')');
            Log::info('serie: ' . strlen($serie) . ' (Value: ' . $serie . ')');
            Log::info('secuencial: ' . strlen($secuencial) . ' (Value: ' . $secuencial . ')');
            Log::info('codNumerico: ' . strlen((string)$codNumerico) . ' (Value: ' . $codNumerico . ')');
            Log::info('tipoEmision: ' . strlen($tipoEmision) . ' (Value: ' . $tipoEmision . ')');
            Log::info('------------------------------------');

            // Cadena de verificación
            $cadenaVerificacion = $formattedDate . $tipoComprobante . $ruc . $tipoAmbiente . $serie . $secuencial . $codNumerico . $tipoEmision;

            Log::info('Total length of cadenaVerificacion: ' . strlen($cadenaVerificacion));

            // Calculo del dígito verificador
            $cod = 2;
            $val = 0;
            for ($i = 47; $i >= 0; $i--) {
                if ($cod > 7)
                    $cod = 2;
                $val += $cadenaVerificacion[$i] * $cod;
                $cod++;
            }

            $res = $val % 11;
            $digitoVerificador = $res == 0 ? 0 : 11 - $res;
            if ($digitoVerificador == 10)
                $digitoVerificador = 1;

            // Concatenar la cadena de verificación y el dígito verificador
            $accessKey = $cadenaVerificacion . $digitoVerificador;

            return $accessKey;
        } catch(\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    protected static function generateNumericCode(): int
    {
        return rand(10000000,99999999);
    }
}
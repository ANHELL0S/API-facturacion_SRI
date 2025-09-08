<?php

namespace App\Services;

use App\Models\User;
use App\Models\PuntoEmision;
use Illuminate\Support\Facades\Log;

class ComprobanteValidator
{
    /**
     * Valida los datos de una factura antes de procesarla
     */
    public function validateFacturaData(array $data, User $user, PuntoEmision $puntoEmision): array
    {
        $errors = [];

        try {
            // 1. Validar información del comprador
            $errors = array_merge($errors, $this->validarComprador($data));

            // 2. Validar detalles de la factura
            $errors = array_merge($errors, $this->validarDetalles($data));

            // 3. Validar totales y cálculos
            $errors = array_merge($errors, $this->validarTotales($data));

            // 4. Validar información tributaria del emisor
            $errors = array_merge($errors, $this->validarEmisor($user));

            // 5. Validar punto de emisión
            $errors = array_merge($errors, $this->validarPuntoEmision($puntoEmision));

            // 6. Validar fecha de emisión
            $errors = array_merge($errors, $this->validarFecha($data));

            // 7. Validar forma de pago
            $errors = array_merge($errors, $this->validarFormaPago($data));

            return [
                'valid' => count($errors) === 0,
                'errors' => $errors
            ];
        } catch (\Exception $e) {
            Log::error("Error en validación de comprobante: " . $e->getMessage());
            return [
                'valid' => false,
                'errors' => ['validation_error' => 'Error interno en validación: ' . $e->getMessage()]
            ];
        }
    }

    private function validarComprador(array $data): array
    {
        $errors = [];

        // Identificación del comprador
        if (!isset($data['identificacionComprador']) || empty($data['identificacionComprador'])) {
            $errors[] = 'identificacionComprador es requerido';
        } else {
            $identificacion = $data['identificacionComprador'];
            $tipoIdentificacion = $data['tipoIdentificacionComprador'] ?? '';

            // Validar formato según tipo de identificación
            switch ($tipoIdentificacion) {
                case '04': // RUC
                    if (!$this->validarRUC($identificacion)) {
                        $errors[] = 'RUC inválido';
                    }
                    break;
                case '05': // Cédula
                    if (!$this->validarCedula($identificacion)) {
                        $errors[] = 'Cédula inválida';
                    }
                    break;
                case '06': // Pasaporte
                    if (strlen($identificacion) < 6 || strlen($identificacion) > 20) {
                        $errors[] = 'Pasaporte debe tener entre 6 y 20 caracteres';
                    }
                    break;
                case '07': // Venta a consumidor final
                    if ($identificacion !== '9999999999999') {
                        $errors[] = 'Para consumidor final debe usar identificación 9999999999999';
                    }
                    break;
                default:
                    $errors[] = 'Tipo de identificación inválido';
            }
        }

        // Razón social del comprador
        if (!isset($data['razonSocialComprador']) || empty($data['razonSocialComprador'])) {
            $errors[] = 'razonSocialComprador es requerido';
        } elseif (strlen($data['razonSocialComprador']) > 300) {
            $errors[] = 'razonSocialComprador no puede exceder 300 caracteres';
        }

        // Dirección del comprador
        if (isset($data['direccionComprador']) && strlen($data['direccionComprador']) > 300) {
            $errors[] = 'direccionComprador no puede exceder 300 caracteres';
        }

        return $errors;
    }

    private function validarDetalles(array $data): array
    {
        $errors = [];

        if (!isset($data['detalles']) || !is_array($data['detalles'])) {
            $errors[] = 'detalles debe ser un array';
            return $errors;
        }

        if (count($data['detalles']) === 0) {
            $errors[] = 'Debe incluir al menos un detalle';
            return $errors;
        }

        foreach ($data['detalles'] as $index => $detalle) {
            $prefix = "detalle[{$index}]";

            // Validaciones obligatorias
            if (!isset($detalle['codigoPrincipal']) || empty($detalle['codigoPrincipal'])) {
                $errors[] = "{$prefix}.codigoPrincipal es requerido";
            }

            if (!isset($detalle['descripcion']) || empty($detalle['descripcion'])) {
                $errors[] = "{$prefix}.descripcion es requerido";
            } elseif (strlen($detalle['descripcion']) > 300) {
                $errors[] = "{$prefix}.descripcion no puede exceder 300 caracteres";
            }

            if (!isset($detalle['cantidad']) || !is_numeric($detalle['cantidad'])) {
                $errors[] = "{$prefix}.cantidad debe ser numérico";
            } elseif ($detalle['cantidad'] <= 0) {
                $errors[] = "{$prefix}.cantidad debe ser mayor a 0";
            }

            if (!isset($detalle['precioUnitario']) || !is_numeric($detalle['precioUnitario'])) {
                $errors[] = "{$prefix}.precioUnitario debe ser numérico";
            } elseif ($detalle['precioUnitario'] < 0) {
                $errors[] = "{$prefix}.precioUnitario no puede ser negativo";
            }

            // Validar descuento
            if (isset($detalle['descuento'])) {
                if (!is_numeric($detalle['descuento'])) {
                    $errors[] = "{$prefix}.descuento debe ser numérico";
                } elseif ($detalle['descuento'] < 0) {
                    $errors[] = "{$prefix}.descuento no puede ser negativo";
                }
            }

            // Validar impuestos
            if (isset($detalle['impuestos']) && is_array($detalle['impuestos'])) {
                foreach ($detalle['impuestos'] as $impIndex => $impuesto) {
                    if (!isset($impuesto['codigo'])) {
                        $errors[] = "{$prefix}.impuestos[{$impIndex}].codigo es requerido";
                    }
                    if (!isset($impuesto['codigoPorcentaje'])) {
                        $errors[] = "{$prefix}.impuestos[{$impIndex}].codigoPorcentaje es requerido";
                    }
                }
            }
        }

        return $errors;
    }

    private function validarTotales(array $data): array
    {
        $errors = [];

        // Validar totalSinImpuestos
        if (!isset($data['totalSinImpuestos']) || !is_numeric($data['totalSinImpuestos'])) {
            $errors[] = 'totalSinImpuestos debe ser numérico';
        } elseif ($data['totalSinImpuestos'] < 0) {
            $errors[] = 'totalSinImpuestos no puede ser negativo';
        }

        // Validar importeTotal
        if (!isset($data['importeTotal']) || !is_numeric($data['importeTotal'])) {
            $errors[] = 'importeTotal debe ser numérico';
        } elseif ($data['importeTotal'] < 0) {
            $errors[] = 'importeTotal no puede ser negativo';
        }

        // Validar que importeTotal >= totalSinImpuestos
        if (isset($data['totalSinImpuestos']) && isset($data['importeTotal'])) {
            if (is_numeric($data['totalSinImpuestos']) && is_numeric($data['importeTotal'])) {
                if ($data['importeTotal'] < $data['totalSinImpuestos']) {
                    $errors[] = 'importeTotal no puede ser menor que totalSinImpuestos';
                }
            }
        }

        // Validar totalDescuento
        if (isset($data['totalDescuento'])) {
            if (!is_numeric($data['totalDescuento'])) {
                $errors[] = 'totalDescuento debe ser numérico';
            } elseif ($data['totalDescuento'] < 0) {
                $errors[] = 'totalDescuento no puede ser negativo';
            }
        }

        return $errors;
    }

    private function validarEmisor(User $user): array
    {
        $errors = [];

        if (!$user->ruc || strlen($user->ruc) !== 13) {
            $errors[] = 'Usuario debe tener RUC válido de 13 dígitos';
        }

        if (!$user->razon_social || empty($user->razon_social)) {
            $errors[] = 'Usuario debe tener razón social configurada';
        }

        if (!in_array($user->ambiente, ['1', '2'])) {
            $errors[] = 'Ambiente del usuario debe ser 1 (pruebas) o 2 (producción)';
        }

        if (!$user->signature_key || !$user->signature_path) {
            $errors[] = 'Usuario debe tener firma electrónica configurada';
        }

        return $errors;
    }

    private function validarPuntoEmision(PuntoEmision $puntoEmision): array
    {
        $errors = [];

        if (!$puntoEmision->activo) {
            $errors[] = 'Punto de emisión debe estar activo';
        }

        if (!$puntoEmision->establecimiento) {
            $errors[] = 'Punto de emisión debe tener establecimiento asociado';
        } elseif (!$puntoEmision->establecimiento->activo) {
            $errors[] = 'Establecimiento debe estar activo';
        }

        if (strlen($puntoEmision->numero) !== 3) {
            $errors[] = 'Número de punto de emisión debe tener 3 dígitos';
        }

        return $errors;
    }

    private function validarFecha(array $data): array
    {
        $errors = [];

        if (isset($data['fechaEmision'])) {
            $fecha = $data['fechaEmision'];

            if (!$this->validarFormatoFecha($fecha)) {
                $errors[] = 'fechaEmision debe tener formato YYYY-MM-DD';
            } else {
                // Validar que la fecha no sea futura (con tolerancia de 1 día)
                $fechaEmision = new \DateTime($fecha);
                $hoy = new \DateTime();
                $manana = $hoy->add(new \DateInterval('P1D'));

                if ($fechaEmision > $manana) {
                    $errors[] = 'fechaEmision no puede ser posterior a mañana';
                }

                // Validar que la fecha no sea muy antigua (máximo 1 año)
                $hace1Ano = new \DateTime();
                $hace1Ano->sub(new \DateInterval('P1Y'));

                if ($fechaEmision < $hace1Ano) {
                    $errors[] = 'fechaEmision no puede ser anterior a 1 año';
                }
            }
        }

        return $errors;
    }

    private function validarFormaPago(array $data): array
    {
        $errors = [];

        if (!isset($data['pagos']) || !is_array($data['pagos'])) {
            $errors[] = 'pagos debe ser un array';
            return $errors;
        }

        if (count($data['pagos']) === 0) {
            $errors[] = 'Debe incluir al menos una forma de pago';
            return $errors;
        }

        foreach ($data['pagos'] as $index => $pago) {
            $prefix = "pagos[{$index}]";

            if (!isset($pago['formaPago'])) {
                $errors[] = "{$prefix}.formaPago es requerido";
            }

            if (!isset($pago['total']) || !is_numeric($pago['total'])) {
                $errors[] = "{$prefix}.total debe ser numérico";
            } elseif ($pago['total'] <= 0) {
                $errors[] = "{$prefix}.total debe ser mayor a 0";
            }
        }

        return $errors;
    }

    /**
     * Validar RUC ecuatoriano
     */
    private function validarRUC(string $ruc): bool
    {
        if (strlen($ruc) !== 13) {
            return false;
        }

        if (!ctype_digit($ruc)) {
            return false;
        }

        // Los últimos 3 dígitos deben ser 001 para persona natural o jurídica
        $ultimosTres = substr($ruc, -3);
        if (!in_array($ultimosTres, ['001'])) {
            return false;
        }

        // Validar dígito verificador (algoritmo del RUC ecuatoriano)
        $digitos = str_split($ruc);
        $tercerDigito = (int) $digitos[2];

        // RUC de sociedad privada o extranjera
        if ($tercerDigito === 9) {
            $coeficientes = [4, 3, 2, 7, 6, 5, 4, 3, 2];
            $suma = 0;
            for ($i = 0; $i < 9; $i++) {
                $suma += (int) $digitos[$i] * $coeficientes[$i];
            }
            $residuo = $suma % 11;
            $digitoVerificador = $residuo === 0 ? 0 : 11 - $residuo;
            return (int) $digitos[9] === $digitoVerificador;
        }

        // RUC de persona natural o jurídica (usar validación de cédula para los primeros 10 dígitos)
        $cedula = substr($ruc, 0, 10);
        return $this->validarCedula($cedula);
    }

    /**
     * Validar cédula ecuatoriana
     */
    private function validarCedula(string $cedula): bool
    {
        if (strlen($cedula) !== 10) {
            return false;
        }

        if (!ctype_digit($cedula)) {
            return false;
        }

        $digitos = array_map('intval', str_split($cedula));
        $provincia = (int) substr($cedula, 0, 2);

        // Validar código de provincia
        if ($provincia < 1 || $provincia > 24) {
            return false;
        }

        // Algoritmo de validación de cédula
        $coeficientes = [2, 1, 2, 1, 2, 1, 2, 1, 2];
        $suma = 0;

        for ($i = 0; $i < 9; $i++) {
            $producto = $digitos[$i] * $coeficientes[$i];
            if ($producto > 9) {
                $producto -= 9;
            }
            $suma += $producto;
        }

        $residuo = $suma % 10;
        $digitoVerificador = $residuo === 0 ? 0 : 10 - $residuo;

        return $digitos[9] === $digitoVerificador;
    }

    /**
     * Validar formato de fecha YYYY-MM-DD
     */
    private function validarFormatoFecha(string $fecha): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $fecha);
        return $d && $d->format('Y-m-d') === $fecha;
    }
}

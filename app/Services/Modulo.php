<?php

namespace App\Services;

use Exception;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;

class Modulo
{
    /**
     * Clave de acceso con 48 digitos
     *
     * @var string
     */
    protected string $numero;

    /**
     * Digito verificador obtenido con el modulo 11
     *
     * @var string
     */
    protected string $digitoVerificador;

    public function __construct($tipoComprobante, $ruc, $ambiente, $serie, $comprobante, $codigo, $fecha = null, $emision = 1)
    {
        $this->numero = "{$this->setFecha($fecha)}{$tipoComprobante}{$ruc}{$ambiente}{$serie}{$comprobante}{$codigo}{$emision}";
        $this->generateDigito();
    }

    /**
     * Generar y obtener clave de acceso con modulo 11
     *
     * @param $tipoComprobante
     * @param $ruc
     * @param $ambiente
     * @param $serie
     * @param $comprobante
     * @param $codigo
     * @param $fecha
     * @param string $emision
     * @return void
     */
    public static function generarClaveAcceso($tipoComprobante, $ruc, $ambiente, $serie, $comprobante, $codigo, $fecha = null, $emision = 1)
    {
        $claveAcceso = new static(
            $tipoComprobante,
            $ruc,
            $ambiente,
            $serie,
            $comprobante,
            $codigo,
            $fecha,
            $emision
        );
        return  $claveAcceso->obtenerClaveAcceso();
    }

    /**
     * Formatear la fecha para la clave de acceso
     *
     * @param $fecha
     * @return void
     */
    public function setFecha($fecha)
    {
        try {
            return Carbon::createFromFormat('dmY', $fecha)->format('dmY');
        } catch (InvalidFormatException $e) {
            throw new Exception("La fecha ingresada no es válida");
        }
    }

    /**
     * Generar digito verificador para la clave de acceso
     *
     * @return string
     */
    public function generateDigito()
    {
        $digito = str_replace(array('.', ','), array('' . ''), strrev($this->numero));
        if (!ctype_digit($digito)) {
            throw new Exception("Para conformar la clave de acceso debe tener solo numeros", 422);
        }
        if (strlen($digito) <> 48) {
            throw new Exception("La clave de acceso no puede ser mayor o menos a 49 digitos", 422);
        }

        $sum = 0;
        $factor = 2;

        for ($i = 0; $i < strlen($digito); $i++) {
            $sum += substr($digito, $i, 1) * $factor;
            if ($factor == 7) {
                $factor = 2;
            } else {
                $factor++;
            }
        }

        $dv = 11 - ($sum % 11);
        if ($dv == 10) {
            return $this->digitoVerificador = 1;
        }

        if ($dv == 11) {
            return $this->digitoVerificador = 0;
        }

        return $this->digitoVerificador = $dv;
    }

    /**
     * obtener la clave de acceso con modulo 11
     *
     * @return void
     */
    public function obtenerClaveAcceso()
    {
        return $this->numero . $this->digitoVerificador;
    }
}

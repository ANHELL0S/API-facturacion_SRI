<?php

namespace App\Services;

use DNS1D;

class ClaveAccesoBarcode
{
    /**
     * Genera un código de barras en formato base64.
     *
     * @param string $claveAcceso
     * @param int $widthFactor
     * @param int $height
     * @return string
     */
    public static function makeBase64(string $claveAcceso, int $widthFactor = 2, int $height = 40): string
    {
        // Genera el código de barras en formato PNG y lo codifica en base64.
        // El formato es Code 128, que es el estándar para la clave de acceso del SRI.
        // El color es negro y no se muestra el texto de la clave de acceso debajo del código.
        return DNS1D::getBarcodePNG($claveAcceso, 'C128', $widthFactor, $height, [0, 0, 0], false);
    }
}

<?php

namespace App\Services;

use App\Models\PuntoEmision;
use App\Models\User;

class DocumentData
{
    public function prepareData(PuntoEmision $puntoEmision, User $user, array $validatedData)
    {
        try {
            // Bloqueo y actualización del secuencial
            $puntoEmision->refresh();

            // Usar el próximo secuencial configurado en el punto de emisión
            $nuevoSecuencial = $puntoEmision->proximo_secuencial;

            $validatedData['secuencial'] = $nuevoSecuencial;
            $validatedData['estab'] = $puntoEmision->establecimiento->numero;
            $validatedData['ptoEmi'] = $puntoEmision->numero;
            $validatedData['dirEstablecimiento'] = $puntoEmision->establecimiento->direccion;

            $validatedData['ambiente'] = $user->ambiente;
            $validatedData['ruc'] = $user->ruc;
            $validatedData['razonSocial'] = $user->razonSocial;
            $validatedData['nombreComercial'] = $user->nombreComercial;
            $validatedData['dirMatriz'] = $user->dirMatriz;
            $validatedData['contribuyenteEspecial'] = $user->contribuyenteEspecial;
            $validatedData['obligadoContabilidad'] = $user->obligadoContabilidad ? 'SI' : 'NO';

            return $validatedData;
        } catch(\Exception $e) {
            throw new \Exception('Error al obtener los datos del punto de emisión: ' . $e->getMessage());
        }
    }
}
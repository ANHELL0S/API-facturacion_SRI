<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class ComprobantePolicy
{
    private function isUserOwner(User $user, $comprobante): bool
    {
        return $comprobante->user_id === $user->id;
    }

    public function view(User $user, $comprobante)
    {
        // Verificar si el usuario es el propietario del comprobante
        if (!$this->isUserOwner($user, $comprobante)) {
            return Response::deny('No tienes permiso para ver este comprobante.');
        }

        return Response::allow();
    }

    public function viewXML(User $user, $comprobante)
    {
        // Verificar si el usuario es el propietario del comprobante
        if (!$this->isUserOwner($user, $comprobante)) {
            return Response::deny('No tienes permiso para ver este comprobante.');
        }

        // Verificar si el comprobante tiene un error registrado
        if ($comprobante->error_code) {
            return Response::deny('El comprobante tiene un error registrado.');
        }

        return Response::allow();
    }
}
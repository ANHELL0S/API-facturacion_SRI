<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Establecimiento;
use App\Models\PuntoEmision;
use Illuminate\Auth\Access\Response;

class PuntoEmisionPolicy
{
    public function view(User $user, PuntoEmision $puntoEmision): Response
    {
        if (!$puntoEmision->establecimiento) {
            return Response::denyWithStatus(404, 'El punto de emisión no tiene un establecimiento asociado.');
        }
        if ($puntoEmision->establecimiento->user_id !== $user->id) {
            return Response::denyWithStatus(403, 'Punto de emisión no pertenece a tu establecimiento.');
        }
        return Response::allow();
    }

    public function create(User $user, int $establecimientoId): Response
    {
        $establecimiento = Establecimiento::where('id', $establecimientoId)
            ->where('user_id', $user->id)
            ->first();

        if (!$establecimiento) {
            return Response::denyWithStatus(404, 'Establecimiento no encontrado o no pertenece al usuario autenticado.');
        }

        return Response::allow();
    }

    public function update(User $user, PuntoEmision $puntoEmision): Response
    {
        return $this->view($user, $puntoEmision);
    }

    public function delete(User $user, PuntoEmision $puntoEmision): Response
    {
        return $this->view($user, $puntoEmision);
    }

    public function reset(User $user, PuntoEmision $puntoEmision): Response
    {
        return $this->view($user, $puntoEmision);
    }
}

<?php

namespace App\Policies;

use App\Models\Establecimiento;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class EstablecimientoPolicy
{
    public function view(User $user, Establecimiento $establecimiento): Response
    {
        if ($user->id !== $establecimiento->user_id) {
            return Response::denyWithStatus(404, 'El establecimiento no pertenece al usuario autenticado.');
        }
        return Response::allow();
    }

    public function update(User $user, Establecimiento $establecimiento): Response
    {
        return $this->view($user, $establecimiento);
    }

    public function delete(User $user, Establecimiento $establecimiento): Response
    {
        return $this->view($user, $establecimiento);
    }
}

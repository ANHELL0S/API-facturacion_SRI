<?php

namespace App\Policies;

use App\Exceptions\FirmaInvalidaException;
use App\Models\User;

class UserPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function firma(User $user): bool
    {
        if (!$user->signature_path || !$user->signature_key) {
            // return false;
            throw new FirmaInvalidaException('El usuario no tiene un certificado de firma digital cargado');
        }

        if ($user->signature_expires_at < now()) {
            // return false;
            throw new FirmaInvalidaException('El certificado de firma digital expiro');
        }

        return true;
    }
}

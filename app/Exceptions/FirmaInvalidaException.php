<?php

namespace App\Exceptions;

use Exception;

class FirmaInvalidaException extends Exception
{
    public function __construct(string $message = "Error de firma")
    {
        parent::__construct($message);
    }
}

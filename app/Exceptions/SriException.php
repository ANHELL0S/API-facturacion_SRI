<?php

namespace App\Exceptions;

use Exception;
use App\Models\ErrorSri;

class SriException extends Exception
{
    protected ?ErrorSri $errorSri;
    protected array $additionalData;

    public function __construct(string $errorCode, string $message = null, array $additionalData = [])
    {
        $this->errorSri = ErrorSri::where('code', $errorCode)->first();
        $this->additionalData = $additionalData;

        $errorMessage = $message
            ?? $this->errorSri?->description
            ?? "Error SRI desconocido: {$errorCode}";

        parent::__construct($errorMessage, (int) $errorCode);
    }

    public function getErrorSri(): ?ErrorSri
    {
        return $this->errorSri;
    }

    public function getValidationType(): ?string
    {
        return $this->errorSri?->validation_type;
    }

    public function getAdditionalData(): array
    {
        return $this->additionalData;
    }
}

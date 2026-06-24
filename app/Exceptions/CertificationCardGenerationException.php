<?php

namespace App\Exceptions;

use Exception;

class CertificationCardGenerationException extends Exception
{
    private array $missingFields = [];

    public function __construct(string $message, array $missingFields = [], ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->missingFields = $missingFields;
    }

    public function getMissingFields(): array
    {
        return $this->missingFields;
    }
}

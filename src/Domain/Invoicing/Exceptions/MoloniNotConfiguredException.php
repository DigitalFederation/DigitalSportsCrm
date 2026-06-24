<?php

namespace Domain\Invoicing\Exceptions;

use Exception;

class MoloniNotConfiguredException extends Exception
{
    public function __construct(
        string $message = 'Moloni is not properly configured. Please complete the configuration in Admin settings.',
        int $code = 0,
        ?Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}

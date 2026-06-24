<?php

namespace Domain\Invoicing\Exceptions;

use Exception;

class MoloniAuthenticationException extends Exception
{
    public function __construct(
        string $message = 'Moloni authentication failed',
        int $code = 401,
        ?Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}

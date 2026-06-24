<?php

namespace App\Exceptions;

use Exception;

/**
 * Class EnrollmentValidationException
 *
 * Custom exception for handling enrollment validation errors.
 */
class EnrollmentValidationException extends Exception
{
    /**
     * EnrollmentValidationException constructor.
     *
     * @param  string  $message  The exception message.
     * @param  int  $code  The exception code.
     * @param  Exception|null  $previous  The previous exception used for exception chaining.
     */
    public function __construct($message = 'Enrollment validation error', $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

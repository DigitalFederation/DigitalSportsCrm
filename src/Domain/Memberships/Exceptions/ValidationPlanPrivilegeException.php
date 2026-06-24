<?php

namespace Domain\Memberships\Exceptions;

use Exception;

class ValidationPlanPrivilegeException extends Exception
{
    public static function insufficientPrivileges(string $memberType, string $requestType, string $reason): self
    {
        return new self("Insufficient validation plan privileges for {$memberType} to {$requestType}: {$reason}");
    }

    public static function unsupportedMemberType(string $memberType): self
    {
        return new self("Unsupported member type: {$memberType}");
    }
}

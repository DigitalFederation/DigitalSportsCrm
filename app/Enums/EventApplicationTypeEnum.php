<?php

namespace App\Enums;

enum EventApplicationTypeEnum: string
{
    case FederationInitiated = 'federation_initiated';
    case DirectSubmission = 'direct_submission';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

<?php

namespace App\Enums;

enum EvtAttachmentTypes: string
{
    case candidature = 'Application Attachments';
    case active = 'Event Attachments';

    case closed = 'Report Attachments';

    public static function toString($type): string
    {
        return match ($type) {
            'candidature' => self::candidature->value,
            'active' => self::active->value,
            'closed' => self::closed->value,
            default => ''
        };
    }
}

<?php

namespace App\Enums;

enum IndividualDocumentTypeEnum: string
{
    case IdentityCard = 'identity_card';
    case CitizenCard = 'citizen_card';
    case ForeignIdentityCard = 'foreign_identity_card';
    case PermanentResidenceCard = 'permanent_residence_card';
    case Passport = 'passport';
    case NationalIdNumber = 'national_id_number';
    case PassportNumber = 'passport_number';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function translationKey(): string
    {
        return match ($this) {
            self::NationalIdNumber => 'profile.national_id_number',
            self::PassportNumber => 'profile.passport_number',
            default => 'individual.doc_types.' . $this->value,
        };
    }
}

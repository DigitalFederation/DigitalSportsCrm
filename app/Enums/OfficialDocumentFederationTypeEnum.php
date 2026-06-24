<?php

namespace App\Enums;

enum OfficialDocumentFederationTypeEnum: string
{
    case Statutes = 'statutes';
    case GovernmentNOCRecognition = 'government_noc_recognition';
    case FederationRepresentatives = 'federation_representatives';
    case OtherDocument = 'other_document';

    public function toString(): string
    {
        return match ($this) {
            self::Statutes => 'Statutes',
            self::GovernmentNOCRecognition => 'Government / NOC Recognition',
            self::FederationRepresentatives => 'Federation Representatives',
            self::OtherDocument => 'Other Document',
        };
    }
}

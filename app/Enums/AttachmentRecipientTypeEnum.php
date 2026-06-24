<?php

namespace App\Enums;

enum AttachmentRecipientTypeEnum: string
{
    case All = 'all';
    case AllFederations = 'all_federations';
    case AllEntities = 'all_entities';
    case AllIndividuals = 'all_individuals';
    case AllEntitiesAndIndividuals = 'all_entities_&_individuals';
    case Individual = 'individual';
    case Entity = 'entity';
    case Federation = 'federation';

    public function toString(): string
    {
        return match ($this) {
            self::All => 'all',
            self::AllFederations => 'all_federations',
            self::AllEntities => 'all_entities',
            self::AllIndividuals => 'all_individuals',
            self::AllEntitiesAndIndividuals => 'all_entities_&_individuals',
            self::Individual => 'individual',
            self::Entity => 'entity',
            self::Federation => 'federation',
        };
    }
}

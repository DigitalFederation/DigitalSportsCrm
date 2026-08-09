<?php

namespace Domain\Federations\Enums;

enum TerritorialResolutionOutcome: string
{
    case RESOLVED = 'resolved';
    case UNRESOLVED = 'unresolved';
    case AMBIGUOUS = 'ambiguous';
}

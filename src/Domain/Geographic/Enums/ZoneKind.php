<?php

namespace Domain\Geographic\Enums;

enum ZoneKind: string
{
    case OPERATIONAL = 'operational';
    case ADMINISTRATIVE_LEVEL_1 = 'administrative_level_1';
}

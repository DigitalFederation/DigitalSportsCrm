<?php

namespace App\Enums;

enum EvtAttributeFillableTypeEnum: string
{
    case AUTO = 'Automatically filled';
    case MANUAL = 'Manual entry';
}

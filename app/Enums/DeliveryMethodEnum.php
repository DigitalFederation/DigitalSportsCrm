<?php

declare(strict_types=1);

namespace App\Enums;

enum DeliveryMethodEnum: string
{
    case Shipped = 'shipped';
    case InPerson = 'in_person';
}

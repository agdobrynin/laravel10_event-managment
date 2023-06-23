<?php

namespace App\Enum;

use OpenApi\Attributes as OA;

#[OA\Schema(
    type: 'string',
)]
enum EventWithCountEnum: string
{
    case ATTENDEES_COUNT = 'attendees';
}

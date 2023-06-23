<?php

namespace App\Enum;

use OpenApi\Attributes as OA;

#[OA\Schema(
    type: 'string',
)]
enum EventLoadRelationEnum: string
{
    case USER = 'user';
    case ATTENDEES = 'attendees';
    case ATTENDEES_USER = 'attendees.user';
}

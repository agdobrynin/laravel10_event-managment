<?php

namespace App\Enum;

use OpenApi\Attributes as OA;

#[OA\Schema(
    type: 'string',
)]
enum AttendeeLoadRelationEnum: string
{
    case USER = 'user';
}


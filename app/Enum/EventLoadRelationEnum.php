<?php

namespace App\Enum;

enum EventLoadRelationEnum: string
{
    case USER = 'user';
    case ATTENDEES = 'attendees';
    case ATTENDEES_USER = 'attendees.user';
    case ATTENDEES_COUNT = 'attendees_count';
}

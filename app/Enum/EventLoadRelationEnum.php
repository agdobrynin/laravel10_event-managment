<?php

namespace App\Enum;

enum EventLoadRelationEnum: string
{
    case USER = 'user';
    case ATTENDEES = 'attendees';
    case ATTENDEES_USER = 'attendees.user';
}

<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Attendee;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;

class AttendeeService
{
    public function store(Event $event, User $user): Attendee
    {
        /** @var Attendee $attendee */
        $attendee = $event->attendees()->make();
        $attendee->user()->associate($user)->save();

        return $attendee;
    }
}

<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Attendee;
use App\Models\Event;
use Illuminate\Http\Request;

class AttendeeService
{
    public function store(Event $event, Request $request): Attendee
    {
        /** @var Attendee $attendee */
        $attendee = $event->attendees()->make();
        $attendee->user()->associate($request->user())->save();

        return $attendee;
    }
}

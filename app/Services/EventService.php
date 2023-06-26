<?php
declare(strict_types=1);

namespace App\Services;

use App\Http\Requests\EventStoreRequest;
use App\Http\Requests\EventUpdateRequest;
use App\Models\Event;
use App\Models\User;

class EventService
{
    public function store(EventStoreRequest $request, User $user): Event
    {
        $event = Event::make($request->validatedToSnake());
        $event->user()->associate($user);
        $event->save();

        return $event;
    }

    public function update(EventUpdateRequest $request, Event $event): Event
    {
        $event->update($request->validatedToSnake());

        return $event;
    }
}

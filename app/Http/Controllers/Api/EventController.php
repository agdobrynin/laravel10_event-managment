<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enum\EventLoadRelationEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\EventLoadRelationRequest;
use App\Http\Requests\EventStoreRequest;
use App\Http\Requests\EventUpdateRequest;
use App\Http\Resources\EventResource;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(EventLoadRelationRequest $request): AnonymousResourceCollection
    {
        $query = Event::query();

        foreach ($request->validated('relation', []) as $relation) {
            $query->when(
                $relation !== EventLoadRelationEnum::ATTENDEES_COUNT->value,
                fn(Builder $builder) => $builder->with($relation)
            );

            if ($relation === EventLoadRelationEnum::ATTENDEES_COUNT->value) {
                $query->withCount(EventLoadRelationEnum::ATTENDEES->value);
            }
        }

        $query = $query->orderBy('start_time', 'desc');

        return EventResource::collection($query->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EventStoreRequest $request): EventResource
    {
        $user = User::findOrFail(1); // TODO make from Request

        $event = new Event($request->validatedToSnake());
        $event->user()->associate($user);
        $event->save();

        return new EventResource($event);
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event): EventResource
    {
        $event->loadMissing(['user']);

        return new EventResource($event);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EventUpdateRequest $request, Event $event): EventResource
    {
        $event->update($request->validatedToSnake());

        return new EventResource($event);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event): Response
    {
        $event->delete();

        return response()->noContent();
    }
}

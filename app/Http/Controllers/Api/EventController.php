<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Dto\LoadRelationAndCountFromRequestDto;
use App\Http\Controllers\Controller;
use App\Http\Requests\EventLoadRelationRequest;
use App\Http\Requests\EventStoreRequest;
use App\Http\Requests\EventUpdateRequest;
use App\Http\Resources\EventResource;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(EventLoadRelationRequest $request): AnonymousResourceCollection
    {
        $eventLoadDto = new LoadRelationAndCountFromRequestDto(...$request->validatedToCamel());

        $query = Event::query()
            ->when($eventLoadDto->relation, fn($query) => $query->with($eventLoadDto->relation))
            ->when($eventLoadDto->withCount, fn($query) => $query->withCount($eventLoadDto->withCount))
            ->orderBy('start_time', 'desc');

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
    public function show(EventLoadRelationRequest $request, int $eventId): EventResource
    {
        $eventLoadDto = new LoadRelationAndCountFromRequestDto(...$request->validatedToCamel());

        $event = Event::query()
            ->when($eventLoadDto->relation, fn($query) => $query->with($eventLoadDto->relation))
            ->when($eventLoadDto->withCount, fn($query) => $query->withCount($eventLoadDto->withCount))
            ->findOrFail($eventId);

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

<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Dto\LoadRelationAndCountFromRequestDto;
use App\Http\Controllers\Controller;
use App\Http\Requests\AttendeeLoadRelationRequest;
use App\Http\Resources\AttendeeResource;
use App\Models\Attendee;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class AttendeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(AttendeeLoadRelationRequest $request, int $eventId): AnonymousResourceCollection
    {
        $dto = new LoadRelationAndCountFromRequestDto(...$request->validatedToCamel());
        $query = Attendee::where('event_id', $eventId)
            ->when($dto->relation, fn($query) => $query->with($dto->relation));

        $attendees = $query->latest()
            ->paginate();

        return AttendeeResource::collection($attendees);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Event $event): AttendeeResource
    {
        $user = User::findOrFail(1); // TODO make from Request
        /** @var Attendee $attendee */
        $attendee = $event->attendees()->make();
        $attendee->user()->associate($user)->save();

        return new AttendeeResource($attendee);
    }

    /**
     * Display the specified resource.
     */
    public function show(AttendeeLoadRelationRequest $request, int $eventId, int $attendeeId): AttendeeResource
    {
        $dto = new LoadRelationAndCountFromRequestDto(...$request->validatedToCamel());

        $query = Attendee::where('event_id', $eventId)
            ->when($dto->relation, fn($query) => $query->with($dto->relation));

        $attendee = $query->findOrFail($attendeeId);

        return new AttendeeResource($attendee);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $eventId, Attendee $attendee): Response
    {
        $attendee->delete();

        return response()->noContent();
    }
}

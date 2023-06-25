<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Dto\LoadRelationAndCountFromRequestDto;
use App\Helpers\ModelLoadRelationCount;
use App\Http\Controllers\Controller;
use App\Http\Requests\AttendeeLoadRelationRequest;
use App\Http\Resources\AttendeeResource;
use App\Models\Attendee;
use App\Models\Event;
use App\Virtual\HttpNotFoundResponse;
use App\Virtual\HttpUnauthorizedResponse;
use App\Virtual\HttpValidationErrorResponse;
use App\Virtual\PaginateMeta;
use App\Virtual\PaginateShort;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

class AttendeeController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum'])
            ->except(['index', 'show']);
        $this->authorizeResource(Attendee::class, 'attendee');
    }

    #[OA\Get(
        path: '/events/{event}/attendees',
        operationId: 'attendeesShowInEvent',
        description: 'Show all attendees in event with relation',
        summary: 'Display the specified resource.',
        tags: ['Attendee']
    )]
    #[OA\PathParameter(name: 'event', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\QueryParameter(ref: '#/components/parameters/relationInAttendees')]
    #[OA\Response(
        response: 200,
        description: 'Success',
        content: [
            new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'data',
                        type: 'array',
                        items: new OA\Items(ref: AttendeeResource::class),
                    ),
                ],
                allOf: [
                    new OA\Schema(ref: PaginateShort::class),
                    new OA\Schema(ref: PaginateMeta::class),
                ]
            )
        ]
    )]
    #[HttpNotFoundResponse]
    #[HttpValidationErrorResponse(description: 'Validation error in query parameters for load relation')]
    public function index(AttendeeLoadRelationRequest $request, int $eventId): AnonymousResourceCollection
    {
        $dto = new LoadRelationAndCountFromRequestDto(...$request->validatedToCamel());
        $query = Attendee::where('event_id', $eventId)
            ->when($dto->relation, fn($query) => $query->with($dto->relation));

        $attendees = $query->latest()
            ->paginate();

        return AttendeeResource::collection($attendees);
    }

    #[OA\Post(
        path: '/events/{event}/attendees',
        operationId: 'attendeesStoreInEvent',
        description: 'Store new attendee to Event',
        summary: 'Store a newly created resource in storage.',
        security: [['apiKeyBearer' => []]],
        tags: ['Attendee']
    )]
    #[OA\PathParameter(name: 'event', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(
        response: 201,
        description: 'Success store new attendee to Event.',
        content: [
            new OA\JsonContent(ref: AttendeeResource::class)
        ]
    )]
    #[HttpUnauthorizedResponse]
    #[HttpNotFoundResponse(description: 'Event not found by id.')]
    public function store(Request $request, Event $event): AttendeeResource
    {
        /** @var Attendee $attendee */
        $attendee = $event->attendees()->make();
        $attendee->user()->associate($request->user())->save();

        return new AttendeeResource($attendee);
    }

    #[OA\Get(
        path: '/events/{event}/attendees/{attendee}',
        operationId: 'attendeesShowFromEvent',
        description: 'Show attendee from Event',
        summary: 'Display the specified resource.',
        tags: ['Attendee']
    )]
    #[OA\PathParameter(name: 'event', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\PathParameter(name: 'attendee', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\QueryParameter(ref: '#/components/parameters/relationInAttendees')]
    #[OA\Response(
        response: 200,
        description: 'Info about attendee with relation',
        content: [
            new OA\JsonContent(ref: AttendeeResource::class)
        ]
    )]
    #[HttpValidationErrorResponse(description: 'Validation error for query string with relate loader')]
    #[HttpNotFoundResponse]
    public function show(AttendeeLoadRelationRequest $request, int $eventId, Attendee $attendee): AttendeeResource
    {
        if ($attendee->event_id !== $eventId) {
            abort(404, 'Attendee not belong to Event with id ' . $eventId);
        }

        $dto = new LoadRelationAndCountFromRequestDto(...$request->validatedToCamel());
        $query = $attendee->newQuery();
        ModelLoadRelationCount::load($query, $dto);

        return new AttendeeResource($query->firstOrFail());
    }

    #[OA\Delete(
        path: '/events/{event}/attendees/{attendee}',
        operationId: 'attendeesDestroyFromEvent',
        description: 'Remove attendee from Event by Attendee owner or Event owner',
        summary: 'Remove the specified resource from storage.',
        security: [['apiKeyBearer' => []]],
        tags: ['Attendee']
    )]
    #[OA\PathParameter(name: 'event', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\PathParameter(name: 'attendee', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(
        response: 204,
        description: 'Delete success',
        content: [
            new OA\JsonContent()
        ]
    )]
    #[HttpUnauthorizedResponse]
    #[HttpNotFoundResponse]
    public function destroy(int $eventId, Attendee $attendee): Response
    {
        $attendee->delete();

        return response()->noContent();
    }
}

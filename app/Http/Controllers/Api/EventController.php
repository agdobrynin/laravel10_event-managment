<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Dto\LoadRelationAndCountFromRequestDto;
use App\Http\Controllers\Controller;
use App\Http\Requests\EventLoadRelationRequest;
use App\Http\Requests\EventStoreRequest;
use App\Http\Requests\EventUpdateRequest;
use App\Http\Requests\EventWithCountRequest;
use App\Http\Resources\EventResource;
use App\Models\Event;
use App\Services\EventService;
use App\Virtual\HttpForbiddenResponse;
use App\Virtual\HttpNotFoundResponse;
use App\Virtual\HttpUnauthorizedResponse;
use App\Virtual\HttpValidationErrorResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

class EventController extends Controller
{
    public function __construct(private readonly EventService $eventService)
    {
        $this->middleware(['auth:sanctum'])
            ->except(['index', 'show']);

        $this->middleware('throttle:api')
            ->only(['store', 'update', 'destroy']);

        $this->authorizeResource(Event::class, 'event');
    }

    #[OA\Get(
        path: '/events',
        operationId: 'eventsList',
        description: 'Get list of events',
        summary: 'List of events with relation loading',
        tags: ['Events']
    )]
    #[OA\QueryParameter(ref: '#/components/parameters/relationInEvent')]
    #[OA\QueryParameter(ref: '#/components/parameters/withCountInEvent')]
    #[OA\Response(
        response: 200,
        description: 'List of events',
        content: new OA\JsonContent(
            ref: EventResource::class,
            type: 'object',
        )
    )]
    #[HttpValidationErrorResponse(description: 'Validation query parameters for relation loading')]
    public function index(
        EventLoadRelationRequest $requestRelation,
        EventWithCountRequest    $requestCount,
    ): AnonymousResourceCollection
    {
        $dto = new LoadRelationAndCountFromRequestDto(
            ...$requestRelation->validatedToCamel(), ...$requestCount->validatedToCamel()
        );

        $query = Event::relationsAndCounts($dto)
            ->sortByStartTime();

        return EventResource::collection($query->get());
    }

    #[OA\Post(
        path: '/events',
        operationId: 'eventsStore',
        description: 'Store new event',
        summary: 'Store a newly created resource in storage.',
        security: [['apiKeyBearer' => []]],
        tags: ['Events']
    )]
    #[OA\RequestBody(
        content: new OA\JsonContent(ref: EventStoreRequest::class, description: 'Data for new event'),
    )]
    #[OA\Response(
        response: 201,
        description: 'Event data',
        content: new OA\JsonContent(
            ref: EventResource::class,
            type: 'object',
        )
    )]
    #[HttpUnauthorizedResponse]
    #[HttpValidationErrorResponse]
    public function store(EventStoreRequest $request): EventResource
    {
        $event = $this->eventService->store($request, $request->user());

        return new EventResource($event);
    }

    #[OA\Get(
        path: '/events/{event}',
        operationId: 'eventsShow',
        description: 'Show Event with relation',
        summary: 'Display the specified resource.',
        tags: ['Events']
    )]
    #[OA\PathParameter(name: 'event', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\QueryParameter(ref: '#/components/parameters/relationInEvent')]
    #[OA\QueryParameter(ref: '#/components/parameters/withCountInEvent')]
    #[OA\Response(
        response: 200,
        description: 'Show event',
        content: new OA\JsonContent(
            ref: EventResource::class,
            type: 'object',
        )
    )]
    #[HttpValidationErrorResponse(description: 'Validation query parameters for relation loading')]
    #[HttpNotFoundResponse]
    public function show(
        EventLoadRelationRequest $requestRelation,
        EventWithCountRequest    $requestCount,
        Event                    $event
    ): EventResource
    {
        $dto = new LoadRelationAndCountFromRequestDto(
            ...$requestRelation->validatedToCamel(), ...$requestCount->validatedToCamel()
        );

        $event->loadRelationsAndCount($dto);

        return new EventResource($event);
    }

    #[OA\Put(
        path: '/events/{event}',
        operationId: 'eventsUpdate',
        description: 'Update event',
        summary: 'Update the specified resource in storage.',
        security: [['apiKeyBearer' => []]],
        tags: ['Events'],
    )]
    #[OA\PathParameter(name: 'event', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(
        content: new OA\JsonContent(ref: EventUpdateRequest::class, description: 'Data for update exist event'),
    )]
    #[OA\Response(
        response: 200,
        description: 'Event data',
        content: new OA\JsonContent(
            ref: EventResource::class,
            type: 'object',
        )
    )]
    #[HttpUnauthorizedResponse]
    #[HttpValidationErrorResponse]
    #[HttpForbiddenResponse]
    #[HttpNotFoundResponse]
    public function update(EventUpdateRequest $request, Event $event): EventResource
    {
        $this->eventService->update($request, $event);

        return new EventResource($event);
    }

    #[OA\Delete(
        path: '/events/{event}',
        operationId: 'eventsDestroy',
        description: 'Delete event with attendees',
        summary: 'Remove the specified resource from storage.',
        security: [['apiKeyBearer' => []]],
        tags: ['Events'],
    )]
    #[OA\Response(
        response: 204,
        description: 'Delete success',
        content: new OA\JsonContent(type: 'string'),
    )]
    #[OA\PathParameter(name: 'event', required: true, schema: new OA\Schema(type: 'integer'))]
    #[HttpUnauthorizedResponse]
    #[HttpForbiddenResponse]
    #[HttpNotFoundResponse]
    public function destroy(Event $event): Response
    {
        $event->delete();

        return response()->noContent();
    }
}

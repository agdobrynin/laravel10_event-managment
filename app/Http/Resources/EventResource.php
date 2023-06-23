<?php
declare(strict_types=1);

namespace App\Http\Resources;

use App\Virtual\DateTimeAtomFormatProperty;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    properties: [
        new OA\Property(
            property: 'data',
            type: 'array',
            items: new OA\Items(
                required: ['id', 'name', 'startTime', 'endTime', 'createdAt', 'updatedAt'],
                properties: [
                    new OA\Property(property: 'id', type: 'integer'),
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'description', type: 'string', nullable: true),
                    new DateTimeAtomFormatProperty(property: 'startTime', description: 'Date and time the Event as ISO format'),
                    new DateTimeAtomFormatProperty(property: 'endTime', description: 'Date and time the Event as ISO format'),
                    new DateTimeAtomFormatProperty(property: 'createdAt'),
                    new DateTimeAtomFormatProperty(property: 'updatedAt'),
                ],
                anyOf: [
                    new OA\Schema(properties: [
                        new OA\Property(
                            property: 'attendees',
                            type: 'array',
                            items: new OA\Items(ref: AttendeeResource::class),
                            minItems: 0,
                        ),
                        new OA\Property(property: 'user', ref: EventUserResource::class, minProperties: 0),
                        new OA\Property(property: 'countAttendees', minProperties: 0, type: 'integer'),
                    ])
                ]
            )
        )
    ]
)]
class EventResource extends JsonResource
{
    protected const DATE_FORMAT = \DateTimeInterface::ATOM;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'startTime' => $this->start_time->format(self::DATE_FORMAT),
            'endTime' => $this->end_time->format(self::DATE_FORMAT),
            'user' => new EventUserResource($this->whenLoaded('user')),
            'attendees' => AttendeeResource::collection($this->whenLoaded('attendees')),
            $this->mergeWhen(!is_null($this->attendees_count), fn() => [
                'countAttendees' => $this->attendees_count,
            ]),
            'createdAt' => $this->created_at->format(self::DATE_FORMAT),
            'updatedAt' => $this->updated_at->format(self::DATE_FORMAT),
        ];
    }
}

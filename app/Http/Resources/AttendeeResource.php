<?php

namespace App\Http\Resources;

use App\Virtual\DateTimeAtomFormatProperty;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    required: ['id', 'createdAt', 'updatedAt'],
    properties: [
        new OA\Property(property: 'id', type: 'integer'),
        new DateTimeAtomFormatProperty(property: 'createdAt'),
        new DateTimeAtomFormatProperty(property: 'updatedAt'),
    ],
    anyOf: [
        new OA\Schema(
            properties: [
                new OA\Property(property: 'user', ref: EventUserResource::class),
            ]
        )
    ]
)]
class AttendeeResource extends JsonResource
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
            'user' => new EventUserResource($this->whenLoaded('user')),
            'createdAt' => $this->created_at->format(self::DATE_FORMAT),
            'updatedAt' => $this->updated_at->format(self::DATE_FORMAT),
        ];
    }
}

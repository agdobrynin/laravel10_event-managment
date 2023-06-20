<?php
declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    protected const DATE_FORMAT_TECH = \DateTimeInterface::ATOM;
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
            'createdAt' => $this->created_at->format(self::DATE_FORMAT_TECH),
            'updatedAt' => $this->updated_at->format(self::DATE_FORMAT_TECH),
        ];
    }
}

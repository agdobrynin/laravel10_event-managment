<?php

namespace App\Http\Requests;

use App\Traits\ValidatedRequestConvertArray;
use App\Virtual\PropertyShortDateTime;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    minProperties: 1,
    type: 'object',
    anyOf: [
        new OA\Schema(
            properties: [
                new OA\Property(property: 'name', type: 'string', maxLength: 255, minLength: 10),
                new OA\Property(property: 'description', type: 'string', minLength: 15, nullable: true),
                new PropertyShortDateTime(property: 'startTime', description: 'date must be after today'),
                new PropertyShortDateTime(property: 'endTime', description: 'date must be after startTime property'),
            ]
        ),
    ],
)]
class EventUpdateRequest extends FormRequest
{
    use ValidatedRequestConvertArray;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'string|min:10|max:255',
            'description' => 'nullable|string|min:15',
            'startTime' => 'date|date_format:Y-m-d H:i|after:today',
            'endTime' => 'date|date_format:Y-m-d H:i|after:startTime',
        ];
    }
}

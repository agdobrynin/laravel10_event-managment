<?php
declare(strict_types=1);

namespace App\Http\Requests;

use App\Traits\ValidatedRequestConvertArray;
use App\Virtual\PropertyShortDateTime;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    properties: [
        new OA\Property(property: 'name', type: 'string', maxLength: 255, minLength: 10),
        new OA\Property(property: 'description', type: 'string', minLength: 15, nullable: true),
        new PropertyShortDateTime(property: 'startTime', description: 'date must be after today'),
        new PropertyShortDateTime(property: 'endTime', description: 'date must be after startTime property'),
    ]
)]
class EventStoreRequest extends FormRequest
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
            'name' => 'required|string|min:10|max:255',
            'description' => 'nullable|string|min:15',
            'startTime' => 'required|date|date_format:Y-m-d H:i|after:today',
            'endTime' => 'required|date|date_format:Y-m-d H:i|after:startTime',
        ];
    }
}

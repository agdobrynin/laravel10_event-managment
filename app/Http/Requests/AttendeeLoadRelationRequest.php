<?php

namespace App\Http\Requests;

use App\Enum\AttendeeLoadRelationEnum;
use App\Traits\ValidatedRequestConvertArray;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use OpenApi\Attributes as OA;

#[OA\QueryParameter(
    parameter: 'relationInAttendees',
    name: 'relation[]',
    description: 'Load relations',
    schema: new OA\Schema(
        type: 'array',
        items: new OA\Items(ref: AttendeeLoadRelationEnum::class)
    )
)]
class AttendeeLoadRelationRequest extends FormRequest
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
            'relation.*' => [
                'string',
                new Enum(AttendeeLoadRelationEnum::class),
            ],
        ];
    }
}

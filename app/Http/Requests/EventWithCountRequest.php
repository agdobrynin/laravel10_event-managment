<?php

namespace App\Http\Requests;

use App\Enum\EventWithCountEnum;
use App\Traits\ValidatedRequestConvertArray;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use OpenApi\Attributes as OA;

#[OA\QueryParameter(
    parameter: 'withCountInEvent',
    name: 'with_count[]',
    description: 'Load count relation',
    schema: new OA\Schema(
        type: 'array',
        items: new OA\Items(ref: EventWithCountEnum::class)
    )
)]
class EventWithCountRequest extends FormRequest
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
            'with_count.*' => [
                'string',
                new Enum(EventWithCountEnum::class)
            ],
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Enum\EventWithCountEnum;
use App\Enum\EventLoadRelationEnum;
use App\Traits\ValidatedRequestConvertArray;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class EventLoadRelationRequest extends FormRequest
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
                new Enum(EventLoadRelationEnum::class),
            ],
            'with_count.*' => [
                'string',
                new Enum(EventWithCountEnum::class)
            ],
        ];
    }
}

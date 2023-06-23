<?php

namespace App\Http\Requests;

use App\Enum\AttendeeLoadRelationEnum;
use App\Traits\ValidatedRequestConvertArray;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

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

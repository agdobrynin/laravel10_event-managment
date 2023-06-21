<?php

namespace App\Http\Requests;

use App\Traits\ValidatedRequestConvertArray;
use Illuminate\Foundation\Http\FormRequest;

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

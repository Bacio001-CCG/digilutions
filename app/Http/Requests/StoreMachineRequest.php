<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreMachineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'daily_price' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Vul een naam in.',
            'daily_price.required' => 'Vul een dagprijs in.',
            'daily_price.numeric' => 'De dagprijs moet een bedrag zijn.',
            'daily_price.min' => 'De dagprijs mag niet negatief zijn.',
            'daily_price.decimal' => 'Gebruik maximaal 2 decimalen.',
        ];
    }
}

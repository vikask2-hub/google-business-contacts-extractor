<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SearchBusinessesRequest extends FormRequest
{
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'city' => ['required', 'string', Rule::in(array_keys(config('gmb_extractor.cities')))],
            'category' => ['required', 'string', Rule::in(array_keys(config('gmb_extractor.categories')))],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'city.required' => 'Choose a city to search.',
            'city.in' => 'Choose a city from the available list.',
            'category.required' => 'Choose a business category to search.',
            'category.in' => 'Choose a category from the available list.',
        ];
    }
}

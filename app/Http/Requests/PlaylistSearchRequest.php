<?php

declare(strict_types=1);

namespace App\Http\Requests;

class PlaylistSearchRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'categories' => [
                'required',
                'array',
                'min:1',
            ],
            'categories.*' => [
                'required',
                'string',
                'max:100',
                'distinct',
            ],
        ];
    }

    /**
     * Prepare the data for validation.
     * This ensures we validate the CLEAN data, not the raw input.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('categories') && is_array($this->categories)) {
            $this->merge([
                'categories' => collect($this->categories)
                    ->map(fn ($item) => is_string($item) ? trim($item) : $item)
                    ->filter(fn ($item) => ! is_null($item) && $item !== '')
                    ->values()
                    ->toArray(),
            ]);
        }
    }

    /**
     * Custom error messages.
     */
    public function messages(): array
    {
        return [
            'categories.required' => __('messages.validation.courses.categories.required'),
            'categories.array' => __('messages.validation.courses.categories.invalid_format'),
            'categories.min' => __('messages.validation.courses.categories.min'),
            'categories.*.required' => __('messages.validation.courses.categories.item_required'),
            'categories.*.string' => __('messages.validation.courses.categories.item_string'),
            'categories.*.max' => __('messages.validation.courses.categories.item_max'),
            'categories.*.distinct' => __('messages.validation.courses.categories.duplicate'),
        ];
    }
}

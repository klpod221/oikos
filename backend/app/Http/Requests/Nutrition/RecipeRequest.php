<?php

namespace App\Http\Requests\Nutrition;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RecipeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'instructions' => ['nullable', 'string'],
            'prep_time' => ['nullable', 'integer', 'min:0'],
            'cooking_time' => ['nullable', 'integer', 'min:0'],
            'servings' => ['nullable', 'integer', 'min:1'],
            'image' => ['nullable', 'string'],
            'ingredients' => ['nullable', 'array'],
            'ingredients.*.id' => ['required', 'exists:ingredients,id'],
            'ingredients.*.quantity' => ['required', 'numeric', 'min:0'],
            'ingredients.*.unit' => ['nullable', 'string'],
        ];
    }
}

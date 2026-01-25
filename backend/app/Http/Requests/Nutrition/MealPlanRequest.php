<?php

namespace App\Http\Requests\Nutrition;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MealPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => ['required', 'date'],
            'meal_type' => ['required', Rule::in(['breakfast', 'lunch', 'dinner', 'snack'])],
            'recipe_id' => ['nullable', 'exists:recipes,id'],
            'description' => ['required_without:recipe_id', 'nullable', 'string'],
            'status' => ['nullable', Rule::in(['planned', 'completed', 'skipped'])],
        ];
    }
}

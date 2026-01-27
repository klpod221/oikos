<?php

namespace App\Http\Requests\Nutrition;

use Illuminate\Foundation\Http\FormRequest;

class IngredientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'unit' => ['nullable', 'string', 'max:50'],
            'image' => ['nullable', 'string'],
            'calories' => ['nullable', 'numeric', 'min:0'],
            'protein' => ['nullable', 'numeric', 'min:0'],
            'carbs' => ['nullable', 'numeric', 'min:0'],
            'fat' => ['nullable', 'numeric', 'min:0'],
            'fiber' => ['nullable', 'numeric', 'min:0'],
            'sugar' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}

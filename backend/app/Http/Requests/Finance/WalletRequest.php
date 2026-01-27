<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class WalletRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Auth middleware handles this
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'currency' => ['required', 'string', 'size:3'], // VND, USD
            'description' => ['nullable', 'string'],
            'is_default' => ['boolean'],
            'icon' => ['nullable', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'max:7'],
            // Initial balance usually set via transaction or initial_balance field,
            // but for simplicity we can allow setting it on create?
            // Better to keep strict accounting: create wallet -> 0 balance -> add 'Initial Balance' transaction.
            // But user friendly apps often allow initial balance.
            'initial_balance' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'default_currency'     => ['required', 'string', Rule::in(Expense::CURRENCIES)],
            'high_spend_threshold' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}

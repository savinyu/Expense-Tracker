<?php

namespace App\Http\Requests;

use App\Models\Expense;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount'       => ['sometimes', 'integer', 'min:1'],
            'currency'     => ['sometimes', 'string', Rule::in(Expense::CURRENCIES)],
            'category'     => ['sometimes', 'string', 'max:100'],
            'expense_date' => ['sometimes', 'date'],
            'description'  => ['nullable', 'string', 'max:255'],
        ];
    }
}

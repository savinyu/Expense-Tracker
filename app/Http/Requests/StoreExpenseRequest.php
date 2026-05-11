<?php

namespace App\Http\Requests;

use App\Models\Expense;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount'       => ['required', 'integer', 'min:1'],
            'currency'     => ['required', 'string', Rule::in(Expense::CURRENCIES)],
            'category'     => ['required', 'string', 'max:100'],
            'expense_date' => ['required', 'date'],
            'description'  => ['nullable', 'string', 'max:255'],
        ];
    }
}

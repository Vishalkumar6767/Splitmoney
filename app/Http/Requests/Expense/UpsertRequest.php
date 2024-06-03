<?php

namespace App\Http\Requests\Expense;

use Illuminate\Foundation\Http\FormRequest;

class UpsertRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {

        return [
            'group_id' => 'required|exists:groups,id',
            'amount' => 'required|numeric',
            'type' => 'required|string|in:EQUALLY,UNEQUALLY',
            'description' => 'nullable',
            'date' => 'date|date_format:Y-m-d',
            'user_expenses' => 'nullable|array',
            'user_expenses.*.user_id' => 'required',
            'user_expenses.*.owned_amount' => 'required_if:type,UNEQUALLY|numeric',
        ];
    }
}

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
            'group_id'=>'required|exists:groups,id',
            'payer_user_id'=>'required|exists:users,id',
            'amount'=>'required|numeric',
            'description'=>'nullable',
            'date'=>'date|date_format:Y-m-d'
           
        ];
    }
}

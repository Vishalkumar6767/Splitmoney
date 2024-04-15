<?php

namespace App\Http\Requests\Group;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Group;



class InviteMembers extends FormRequest
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
            'email' => 'required|email|unique:invite_group_members,email',
        ];
    }
    public function groups()
    {
        return $this->hasMany(Group::class, 'user_id');
    }
}
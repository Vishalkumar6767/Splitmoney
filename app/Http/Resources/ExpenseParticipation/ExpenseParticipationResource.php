<?php

namespace App\Http\Resources\ExpenseParticipation;

use Illuminate\Http\Request;

use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseParticipationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'user_id' => $this->user_id,
            'expense_id' => $this->expense_id,
            'owned_amount' => $this->owned_amount
        ];
    }
}

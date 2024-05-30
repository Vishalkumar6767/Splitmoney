<?php

namespace App\Http\Resources\Expense;

use App\Http\Resources\ExpenseParticipation\ExpenseParticipationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'group_id' => $this->group_id,
            'payer_user_id' => $this->payer_user_id,
            'amount' => $this->amount,
            'description' =>$this->description,
            'date' =>$this->date,
            // 'user_expenses'=> ExpenseParticipationResource::collection($this->Expenses)

        ];
    }
}

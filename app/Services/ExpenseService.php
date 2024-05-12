<?php

namespace App\Services;

use App\Models\Expense;
use Illuminate\Support\Facades\DB;

class ExpenseService
{
    protected $expenseObject;

    public function __construct(Expense $expenseObject)
    {
        $this->expenseObject = $expenseObject;
    }

    public function collection($inputs)
    {
        $includes = [];
        if (!empty($inputs['includes'])) {
            $includes = explode(",", $inputs['includes']);
        }
        $expenses = $this->expenseObject->with($includes);
        $expenses = $expenses->with('userExpenses');
        $expenses = $expenses->where('group_id', $inputs['group_id'])->get();
        return $expenses;
    }


    public function store($inputs)
    {
        DB::beginTransaction();
        $expense = $this->expenseObject->create([
            'group_id' => $inputs['group_id'],
            'payer_user_id' => $inputs['payer_user_id'],
            'amount' => $inputs['amount'],
            'type' => $inputs['type'],
            'description' => $inputs['description'],
            'date' => $inputs['date']
        ]);
        $this->addUserExpenses($inputs, $expense);
        DB::commit();
        return ['message' => "Data added successfully"];
    }

    public function resource($id)
    {
        return $this->expenseObject->findOrFail($id);
    }

    public function update($id, $inputs)
    {
        DB::beginTransaction();
        $expense = $this->expenseObject->findOrFail($id);
        $expense->update([
            'group_id' => $inputs['group_id'],
            'payer_user_id' => $inputs['payer_user_id'],
            'amount' => $inputs['amount'],
            'type' => $inputs['type'],
            'description' => $inputs['description'],
            'date' => $inputs['date']
        ]);
        $this->addUserExpenses($inputs, $expense);
        DB::commit();
        return ['message' => "Data updated successfully"];
    }

    public function delete($id)
    {
        $expense = $this->resource($id);
        $expense->userExpenses()->delete();
        $expense->delete();
        return ['message' => "Expense deleted successfully"];
    }

    protected function addUserExpenses($inputs, $expense)
    { 
        $expense->userExpenses()->delete();
        
        if (isset($inputs['user_expenses'])) {
            if ($inputs['type'] === "EQUALLY") {
                $ownedUserAmount = $expense->amount / count($inputs['user_expenses']);
                foreach ($inputs['user_expenses'] as $userExpense) {
                    $expense->userExpenses()->create([
                        'user_id' => $userExpense['user_id'],
                        'expense_id' => $expense->id,
                        'owned_amount' => $ownedUserAmount
                    ]);
                }
            } elseif ($inputs['type'] === "UNEQUALLY") {
                foreach ($inputs['user_expenses'] as $userExpense) {
                    $expense->userExpenses()->create([
                        'user_id' => $userExpense['user_id'],
                        'expense_id' => $expense->id,
                        'owned_amount' => $userExpense['owned_amount']
                    ]);
                }
            }else{
                $errors['errors'] = "Invalid amount type.";
                
            }
        }
    }
    
}

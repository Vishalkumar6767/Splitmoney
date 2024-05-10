<?php

namespace App\Services;

use App\Models\Expense;
use Illuminate\Support\Facades\DB;

class ExpenseService
{
    /**
     * Create a new class instance.
     */
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
        $data = $this->expenseObject->with($includes);
        $data = $data->with('userExpenses');
        $data = $data->where('group_id', $inputs['group_id'])->get();
        return $data;
    }

    public function store($inputs)
    {
        DB::beginTransaction();
        $expense = $this->expenseObject->create([
            'group_id' => $inputs['group_id'],
            'payer_user_id' => $inputs['payer_user_id'],
            'amount' => $inputs['amount'],
            'description' => $inputs['description'],
            'date' => $inputs['date']
        ]);
        $this->addUserExpenses($inputs, $expense);
        DB::commit();
        $success['message'] = "Data added successfully";
        return $success;
    }

    public function resource($id)
    {
        $data = $this->expenseObject->findOrFail($id);
        return $data;
    }

    public function update($id, $inputs)
    {
        DB::beginTransaction();
        $expense = $this->resource($id)->update([
            'group_id' => $inputs['group_id'],
            'payer_user_id' => $inputs['payer_user_id'],
            'amount' => $inputs['amount'],
            'description' => $inputs['description'],
            'date' => $inputs['date']
        ]);
        $this->addUserExpenses($inputs, $expense);
        DB::commit();
        $success['message'] = "Data updated successfully";
        return $success;
    }

    public function delete($id)
    {

        $expense = $this->resource($id);

        $expense->userExpenses()->delete();
        $expense->delete();
        $success['message'] = "Expense deleted successfully";
        return $success;
    }

    protected function addUserExpenses($inputs, $expense)
    {

        if (!empty($inputs['user_expenses'])) {
            $expense->userExpenses()->delete();
            $test = null;
            foreach ($inputs['user_expenses'] as $userExpense) {
                // $test += $userExpense['owned_amount'];
                // $sumAmount = array_sum(array_column($userExpense,'owned_amount'));            
                $expenseUser =  $expense->userExpenses()->create([
                    'user_id' => $userExpense['user_id'],
                    'user_expense' => $expense->id,
                    'owned_amount' => $userExpense['owned_amount']
                ]);
            }
        }
    }
}

<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\User;
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
        $expenses = $this->expenseObject->with($includes, ['userExpenses'])
            ->where('group_id', $inputs['group_id'])->get();
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
        $success['message'] = "Data added successfully";
        return $success;
    }

    public function resource($id)
    {
        return $this->expenseObject->findOrFail($id);
    }

    public function update($id, $inputs)
    {
        DB::beginTransaction();
        $expense = $this->resource($id);
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
        if (isset($inputs['user_expenses'])) {
            $expense->userExpenses()->delete();
            if ($inputs['type'] === "EQUALLY") {
                $ownedUserAmount = $expense->amount / count($inputs['user_expenses']);
                foreach ($inputs['user_expenses'] as $userExpense) {
                    $expense->userExpenses()->create([
                        'user_id' => $userExpense['user_id'],
                        'expense_id' => $expense->id,
                        'owned_amount' => $ownedUserAmount
                    ]);
                }
            }
            if ($inputs['type'] === "UNEQUALLY") {
                foreach ($inputs['user_expenses'] as $userExpense) {
                    $expense->userExpenses()->create([
                        'user_id' => $userExpense['user_id'],
                        'expense_id' => $expense->id,
                        'owned_amount' => $userExpense['owned_amount']
                    ]);
                }
            }
        }
    }

    public function storeGroupStatistics($groupId){
         $total = Expense::where('group_id',$groupId)->select('amount')->sum('amount');
        dd($total);


    }
    public function resourceGroupStatistics($groupId){
        $expenseDetails = Expense::where('group_id',$groupId)->with("user")->get();
        $total = $expenseDetails->select('amount')->sum('amount');
       
        return [
            'user'=> $expenseDetails,
            'total'=>$total
        ];

    }
}

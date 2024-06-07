<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\ExpenseParticipation;
use App\Models\Group;
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
        $expenses = $this->expenseObject->with($includes)
            ->where('group_id', $inputs['group_id'])->orderBy('created_at','desc')->get();
        return $expenses;
    }

    public function store($inputs)
    {
        DB::beginTransaction();
        $expense = $this->expenseObject->create([
            'group_id' => $inputs['group_id'],
            'payer_user_id' => auth()->id(),
            'amount' => $inputs['amount'],
            'type' => $inputs['type'],
            'description' => $inputs['description'],
            'date' => $inputs['date'],
        ]);
        $totalSharedAmount = array_sum((array_column($inputs['user_expenses'], 'owned_amount')));
        if ($inputs['type'] === "UNEQUALLY" &&  $expense->amount !== $totalSharedAmount) {
            $error['errors'] = [
                'message' => "Kindly check shared amount.",
                'code' => 400
            ];
            return $error;
        }
        $this->addUserExpenses($inputs, $expense);
        DB::commit();
        $success['message'] = "Data added successfully";
        return $success;
    }

    public function resource($id,$inputs)
    {

        $includes = [];
      
        if (!empty($inputs['includes'])) {
            $includes = explode(",", $inputs['includes']);
        }
        $expense = $this->expenseObject->with($includes)->findOrFail($id);

        return $expense;
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
        $totalSharedAmount = array_sum((array_column($inputs['user_expenses'], 'owned_amount')));
        if ($inputs['type'] === "UNEQUALLY" &&  $expense->amount !== $totalSharedAmount) {
            $error['errors'] = [
                'message' => "Kindly check shared amount.",
                'code' => 400
            ];
            return $error;
        }
        $this->addUserExpenses($inputs, $expense);
        DB::commit();
        $success['message'] = "Data updated successfully";
        return $success;
    }

    public function delete($id)
    {
        $expense = $this->expenseObject->findOrFail($id);
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
                $totalSharedAmount = array_sum((array_column($inputs['user_expenses'], 'owned_amount')));
                if ($expense->amount !== $totalSharedAmount) {
                    $error['errors'] = [
                        'message' => "Kindly check shared amount.",
                        'code' => 400
                    ];
                    return $error;
                }
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

    public function resourceGroupStatistics($groupId)
{
    $groupDetails = Group::with(['members', 'expenses'])->find($groupId);
    $groupStatistics = [];
    foreach ($groupDetails->members as $member) {
        $lentByMember = $groupDetails->expenses()
            ->where('payer_user_id', $member->id) 
            ->sum('amount');
        $borrowedByMember = ExpenseParticipation::where('user_id', $member->id)
            ->whereIn('expense_id', $groupDetails->expenses->pluck('id'))
            ->sum('owned_amount');
        if ($lentByMember > $borrowedByMember) {
            $remainingAmountToGet = $lentByMember - $borrowedByMember;
            $groupStatistics[] = [
                'user' => $member,
                'expense' => [
                    'total' => $remainingAmountToGet,
                    'type' => "DEBT",
                ],
            ];
        }
        if ($lentByMember < $borrowedByMember) {
            $remainingAmountToPay = $borrowedByMember - $lentByMember;
            $groupStatistics[] = [
                'user' => $member,
                'expense' => [
                    'total' => $remainingAmountToPay,
                    'type' => "CREDIT",
                ],
            ];
        }
    }
    return $groupStatistics;
}

}

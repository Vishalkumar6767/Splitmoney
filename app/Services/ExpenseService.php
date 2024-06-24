<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\ExpenseParticipation;
use App\Models\Group;
use App\Models\Settlement;
use DivisionByZeroError;
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
    
        if (!in_array('userExpenses', $includes)) {
            $includes[] = 'userExpenses';
        }
    
        $user = auth()->user();
        $query = $this->expenseObject->with($includes)
            ->where('group_id', $inputs['group_id'])
            ->orderBy('created_at', 'desc');
    
        // Filter out private expenses that the user is not part of
        $query->where(function ($q) use ($user) {
            $q->where('is_private', false)
                ->orWhere(function ($q) use ($user) {
                    $q->where('is_private', true)
                        ->where(function ($q) use ($user) {
                            $q->where('payer_user_id', $user->id)
                                ->orWhereHas('userExpenses', function ($q) use ($user) {
                                    $q->where('user_id', $user->id);
                                });
                        });
                });
        });
    
        $expenses = $query->get();
    
        // Calculate lent and borrowed amounts
        foreach ($expenses as $expense) {
            $expenseLentAmount = 0;
            $expenseBorrowedAmount = 0;
    
            if (!empty($expense->userExpenses) && is_iterable($expense->userExpenses)) {
                foreach ($expense->userExpenses as $userExpense) {
                    if ($expense->payer_user_id == $userExpense->user_id) {
                        $expenseBorrowedAmount += $userExpense->owned_amount;
                    } else {
                        $expenseLentAmount += $userExpense->owned_amount;
                    }
                }
            }
    
            $expense->you_borrowed = $expenseBorrowedAmount;
            $expense->you_lent = $expenseLentAmount;
        }
    
        return $expenses;
    }
    
    public function store($inputs)
    {
        DB::beginTransaction();
        $isPrivate = isset($inputs['is_private']) ? $inputs['is_private'] : false;
        $expense = $this->expenseObject->create([
            'group_id' => $inputs['group_id'],
            'payer_user_id' => $inputs['payer_user_id'],
            'amount' => $inputs['amount'],
            'type' => $inputs['type'],
            'description' => $inputs['description'],
            'date' => $inputs['date'],
            'is_private' =>$isPrivate,
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

    public function resource($id, $inputs)
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
        $settlement = Settlement::where('expense_id', $id)->delete();

        $expense = $this->expenseObject->findOrFail($id);
        $expense->userExpenses()->delete();
        $expense->delete();
        $success['message'] = "Expense deleted successfully";
        return $success;
    }

    protected function addUserExpenses($inputs, $expense)
{
    try {
        if (isset($inputs['user_expenses'])) {
            if (count($inputs['user_expenses']) === 0) {
                return $error['errors'] = [
                    'message' => "Please add at least one member.",
                    'code' => 400
                ];
            }

            $expense->userExpenses()->delete();

            if ($inputs['type'] === "EQUALLY") {
                if (count($inputs['user_expenses']) > 0) {
                    $ownedUserAmount = $expense->amount / count($inputs['user_expenses']);
                    foreach ($inputs['user_expenses'] as $userExpense) {
                        $expense->userExpenses()->create([
                            'user_id' => $userExpense['user_id'],
                            'expense_id' => $expense->id,
                            'owned_amount' => $ownedUserAmount
                        ]);
                    }
                }
            }

            if ($inputs['type'] === "UNEQUALLY") {
                $totalSharedAmount = array_sum(array_column($inputs['user_expenses'], 'owned_amount'));
                if ($expense->amount !== $totalSharedAmount) {
                    return $error['errors'] = [
                        'message' => "Kindly check shared amount.",
                        'code' => 400
                    ];
                }
                foreach ($inputs['user_expenses'] as $userExpense) {
                    $expense->userExpenses()->create([
                        'user_id' => $userExpense['user_id'],
                        'expense_id' => $expense->id,
                        'owned_amount' => $userExpense['owned_amount']
                    ]);
                }
            }
        } else {
            return $data['errors'] = [
                'message' => "User expenses data is missing.",
                'code' => 400
            ];
        }
    } catch (DivisionByZeroError $e) {
        return $error['errors'] = [
            'message' => "Division by zero error: Please ensure there are user expenses provided.",
            'code' => 400
        ];
    } catch (\Exception $e) {
        return $errors['errors'] = [
            'message' => "An unexpected error occurred: " . $e->getMessage(),
            'code' => 500
        ];
    }
}

    public function resourceGroupStatistics($groupId)
    {
        $groupDetails = Group::with(['members', 'expenses.userExpenses', 'settlements'])->find($groupId);
        $groupStatistics = [];
    
        if (!$groupDetails) {
            return ['message' => 'Group not found'];
        }
    
        foreach ($groupDetails->members as $member) {
            $lentByMember = $groupDetails->expenses()
                ->where('payer_user_id', $member->id)
                ->where('type', '!=', 'SETTLEMENT')
                ->sum('amount');
            $borrowedByMember = ExpenseParticipation::where('user_id', $member->id)
                ->whereIn('expense_id', $groupDetails->expenses->pluck('id'))
                ->sum('owned_amount');
            $settlementAmountReceivedByMember = $groupDetails->settlements()
                ->where('payee_id', $member->id)
                ->sum('amount');
            $settlementAmountPaidByMember = $groupDetails->settlements()
                ->where('payer_user_id', $member->id)
                ->sum('amount');
            $netAmount = $lentByMember - $borrowedByMember + $settlementAmountPaidByMember - $settlementAmountReceivedByMember;
            $type = ($netAmount > 0) ? "DEBT" : (($netAmount < 0) ? "CREDIT" : "BALANCED");
            if ($lentByMember == 0 && $borrowedByMember == 0 && $settlementAmountReceivedByMember == 0 && $settlementAmountPaidByMember == 0) {
                return ['message' => "No transaction Yet"];
            }
    
            $groupStatistics[] = [
                'user' => $member,
                'expense' => [
                    'total' => abs($netAmount),
                    'type' => $type,
                ],
            ];
        }
    
        return $groupStatistics;
    }

}

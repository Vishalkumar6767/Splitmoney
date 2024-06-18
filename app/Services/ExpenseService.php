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
        $expenses = $this->expenseObject->with($includes)
            ->where('group_id', $inputs['group_id'])->orderBy('created_at', 'desc')->get();
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
                // Check if user_expenses is not empty
                if (count($inputs['user_expenses']) === 0) {
                    return $error['errors'] = [
                        'message' => "Please add at least one member.",
                        'code' => 400
                    ];
                }
                // Delete existing user expenses
                $expense->userExpenses()->delete();

                if ($inputs['type'] === "EQUALLY") {
                    // Ensure there is at least one user expense to avoid division by zero
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
                    // Calculate the total shared amount
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

        foreach ($groupDetails->members as $member) {
            // Calculate total amount lent by the member (excluding settlements)
            $lentByMember = $groupDetails->expenses()
                ->where('payer_user_id', $member->id)
                ->where('type', '!=', 'SETTLEMENT')
                ->sum('amount');

            // Calculate total amount borrowed by the member
            $borrowedByMember = ExpenseParticipation::where('user_id', $member->id)
                ->whereIn('expense_id', $groupDetails->expenses->pluck('id'))
                ->sum('owned_amount');
                if($borrowedByMember === 0 & $lentByMember === 0){
                   
                        $message['message'] = "No transaction Yet";
                        return $message;
                   
                }

            // Calculate total settlement amount received by the member
            $settlementAmountReceivedByMember = $groupDetails->settlements()
                ->where('payee_id', $member->id)
                ->sum('amount');

            // Calculate total settlement amount paid by the member
            $settlementAmountPaidByMember = $groupDetails->settlements()
                ->where('payer_user_id', $member->id)
                ->sum('amount');

            // Calculate net amount (lent - borrowed - received settlements + paid settlements)
            $netAmount = $lentByMember - $borrowedByMember + $settlementAmountPaidByMember - $settlementAmountReceivedByMember;

            // Check if the user wants to settle their credit amount
            if ($netAmount < 0 && abs($netAmount) <= $settlementAmountPaidByMember) {
                // Adjust net amount to zero if the user has settled their credit amount
                $netAmount = 0;
            }

            // Determine the type of balance
            $type = ($netAmount > 0) ? "DEBT" : (($netAmount < 0) ? "CREDIT" : "BALANCED");

            // Add member's financial details to the group statistics
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

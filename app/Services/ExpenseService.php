<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\ExpenseParticipation;
use App\Models\Group;
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
            'payer_user_id' => auth()->id(),
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
            'payer_user_id' => auth()->id(),
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

    public function resourceGroupStatistics($groupId){
        $userId = auth()->id();
        $userDetails = Group::where('id',$groupId)->with(['members','expense'])->get();
        $totalsDebitAmount = Expense::where('payer_user_id',$userId)->with("userExpenses")->select('amount')->sum('amount');
        $expenseIds = Expense::select('id')->where('payer_user_id', $userId)->pluck('id');
        foreach ($expenseIds as $expenseId) {
            $totalOwnedAmount = ExpenseParticipation::where('user_id', $userId)
                                           ->where('expense_id', $expenseId)
                                           ->sum('owned_amount');
    
        }
        
        $groupStatistics = [];
        foreach($userDetails as$userDetail){

            if($totalsDebitAmount > $totalOwnedAmount){
                $remainingAmountToGet = $totalsDebitAmount - $totalOwnedAmount;
                $groupStatistics[] =[
                    'data'=>[
                        'user'=>$userDetail->members,
                        'expense'=>[
                            'total'=>$totalsDebitAmount,
                            'type'=>"DEBT",
                        ],
                        'obtained'=>[
                            'total'=> $totalOwnedAmount,
                            'type'=> "CREDIT"
                        ],
                        'toGet'=>$remainingAmountToGet
                    ]
                        ];
              }else{
                $remainingAmountToPay = $totalOwnedAmount-$totalsDebitAmount;
                $groupStatistics[] =[
                    'data'=>[
                        'user'=>$userDetail->members,
                        'expense'=>[
                            'total'=>$totalsDebitAmount,
                            'type'=>"DEBT",
                        ],
                        'obtained'=>[
                            'total'=> $totalOwnedAmount,
                            'type'=> "CREDIT"
                        ],
                        'toPay'=>$remainingAmountToPay,
                        
                    ]
                        ];
              }

            // $groupStatistics[] =[
            //     'data'=>[
            //         'user'=>$userDetail->members,
            //         'expense'=>[
            //             'total'=>$totalsDebitAmount,
            //             'type'=>"DEBT",
            //         ],
            //         'obtained'=>[
            //             'total'=> $totalOwnedAmount,
            //             'type'=> "CREDIT"
            //         ],
            //         'toPay'=>$remainingAmountToPay,
            //         'toGet'=>$remainingAmountToGet
            //     ]
            //         ];
        }
        return $groupStatistics;
       

    }
}

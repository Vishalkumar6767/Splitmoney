<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Settlement;
use Illuminate\Support\Facades\DB;

class SettlementService
{
    /**
     * Create a new class instance.
     */
    private $expenseObject;
    private $settlementObject;
    public function __construct()
    {
        $this->expenseObject = new Expense;
        $this->settlementObject = new Settlement;
    }
    public function collection($inputs)
    {
        $includes = [];
        if (!empty($inputs['includes'])) {
            $includes = explode(",", $inputs['includes']);
        }

        $settlements = $this->settlementObject->with($includes)
            ->where('group_id', $inputs['group_id'])->orderBy('created_at', 'desc')->get();
        return $settlements;
    }
    public function store($inputs)
    {
        DB::beginTransaction();
        $expense = $this->expenseObject->create([
            'group_id' => $inputs['group_id'],
            'payer_user_id' => auth()->id(),
            'amount' => $inputs['amount'],
            'type' => "SETTLEMENT",
            'description' => $inputs['description'],
            'date' => now(),

        ]);
        $this->addSettlement($inputs, $expense);
        $success['message'] = "Data added successfully";
        DB::commit();
        return $success;
    }
    protected function addSettlement($inputs, $expense)
    {
        if ($expense->type === "SETTLEMENT") {
            $expense->settlements()->create([
                'group_id' => $expense->group_id,
                'expense_id' => $expense->id,
                'payer_user_id' => auth()->id(),
                'payee_id' => $inputs['payee_id'],
                'amount' => $inputs['amount'],
            ]);
        }
    }

    public function resource($id, $inputs)
    {
        $includes = [];
        if (!empty($inputs['includes'])) {
            $includes = explode(",", $inputs['includes']);
        }
        $settlements = $this->settlementObject->with($includes)->findOrFail($id);
        $totalAmountPayerLent = $this->expenseObject->where('group_id', $settlements->group_id)
            ->where('payer_user_id', auth()->id())
            ->where('type', '!=', 'SETTLEMENT')
            ->sum('amount');
        $totalAmountPayeeLent = $this->expenseObject->where('group_id', $settlements->group_id)
            ->where('payer_user_id', $settlements->payee_id)
            ->where('type', '!=', 'SETTLEMENT')
            ->sum('amount');
        $netAmount = $totalAmountPayerLent - $totalAmountPayeeLent;
        $totalAmountSettledByPayer = $this->expenseObject->where('group_id', $settlements->group_id)
            ->where('payer_user_id', auth()->id())
            ->where('type', 'SETTLEMENT')
            ->sum('amount');
        $netAmountAfterSettledByPayer = $netAmount - $totalAmountSettledByPayer;
        $type = ($netAmountAfterSettledByPayer > 0) ? "lent" : (($netAmountAfterSettledByPayer < 0) ? "borrowed" : "Balanced");
        $settlements->groupStatistics = [
            'amount' => abs( $netAmountAfterSettledByPayer),
            'type' => $type,
        ];
        return $settlements;
    }
    public function delete($id){
        $settlements = $this->settlementObject->findOrFail($id);
       $settlements ->delete();
        $success['message'] = "data Deleted Successfully";
        return $success;
    }
}

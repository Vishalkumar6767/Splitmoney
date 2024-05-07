<?php

namespace App\Services;

use App\Models\Expense;

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
        if (!empty($inputs['includes']))
        {
            $includes = explode(",", $inputs['includes']);
        }
        $data = $this->expenseObject->with($includes);
        $data = $data->where('group_id',$inputs['group_id'])->get();
        return $data;
    }

    public function store($inputs)
    {
        $this->expenseObject->create([
            'group_id' => $inputs['group_id'],
            'payer_user_id' => $inputs['payer_user_id'],
            'amount' => $inputs['amount'],
            'description' => $inputs['description'],
            'date' => $inputs['date']
        ]);
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
        $data = $this->resource($id);
        $data->update($inputs);
        $success['message'] = "Data updated successfully";
        return $success;
    }

    public function delete($id)
    {
        $data = $this->resource($id);
        $data->delete();
        $success['message'] = "Expense deleted successfully";
        return $success;
    }
}

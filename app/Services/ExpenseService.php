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

    public function collection()
    {
        $data = $this->expenseObject->all();
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
        if (empty($data)) {
            $error['errors'] = [
                'message' => "Expense not found",
                'code' => 400
            ];
            return $error;
        }
        $data->update($inputs);
        $success['message'] = "Data updated successfully";
        return $success;
    }

    public function delete($id)
    {
        $data = $this->resource($id);
        if (isset($data['errors'])) {
            return $data;
        }
        $data->delete();
        $success['message'] = "Expense deleted successfully";
        return $success;
    }
}

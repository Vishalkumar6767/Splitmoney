<?php

namespace App\Http\Controllers;

use App\Http\Requests\Expense\UpsertRequest;
use App\Services\ExpenseService;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    protected $expenseService;

    public function __construct(ExpenseService $expenseService)
    {
        $this->expenseService = $expenseService;
    }

    public function index()
    {
        $data = $this->expenseService->collection();
        if (isset(($data['errors']))) {
            return response()->json($data['errors'], 400);
        }
        return response()->json($data, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UpsertRequest $request)
    {
        $data = $this->expenseService->store($request->validated());
        if (isset($data['errors'])) {
            return response()->json($data['errors'], 400);
        }
        return response()->json($data, 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        $data = $this->expenseService->resource($id);
        if (isset($data['errors'])) {
            return response()->json($data['errors'], 400);
        }
        return response()->json($data, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(int $id, UpsertRequest $request)
    {
        $data = $this->expenseService->update($id, $request->validated());
        if (isset($data['errors'])) {
            return response()->json($data['errors'], 400);
        }
        return response()->json($data, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = $this->expenseService->delete($id);
        if (isset($data['errors'])) {
            return response()->json($data['errors'], 400);
        }
        return response()->json($data, 200);
    }
}

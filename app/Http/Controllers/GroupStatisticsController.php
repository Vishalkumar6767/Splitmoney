<?php

namespace App\Http\Controllers;

use App\Http\Requests\Expense\GroupStatisticsRequest;
use App\Services\ExpenseService;
use Illuminate\Http\Request;

class GroupStatisticsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    protected $expenseService;
    public function __construct(ExpenseService $expenseService){
        $this->expenseService = $expenseService;
    }
    public function index(Request $request)
    {
        $data = $this->expenseService->collection($request->all());
        if(isset($data['errors'])){
            return response()->json($data['errors'], 400);
        }
        return response()->json($data,200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(GroupStatisticsRequest $request)
    {
        $data = $this->expenseService->storeGroupStatistics($request->validated());
        if(isset($data['errors'])){
            return response()->json($data['errors'],400);
        }
        return response()->json($data,200);  
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = $this->expenseService->resourceGroupStatistics($id);
        if(isset($data['errors'])){
            return response()->json($data['errors'],400);
        }
        return response()->json($data,200);
    }
  
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

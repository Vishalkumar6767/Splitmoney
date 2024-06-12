<?php

namespace App\Http\Controllers;

use App\Http\Requests\Expense\SettlementsRequest;
use App\Services\SettlementService;
use Illuminate\Http\Request;

class SettlementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    private $settlementService;

    public function __construct(SettlementService $settlementService)
    {
        $this->settlementService = $settlementService;
    }
    public function index(Request $request)
    {
        $data = $this->settlementService->collection($request->all());
        if(isset($data['errors'])){
            return response()->json($data['errors'], 400);
        }
        return response()->json($data, 200);
    }

    public function store(SettlementsRequest $request)
    {
        $data = $this->settlementService->store($request->validated());
        if(isset($data['errors'])){
            return response()->json($data['errors'], 400);
        }
        return response()->json($data, 200);
       
    }
    public function show(int $id, Request $request)
    {
        $data = $this->settlementService->resource($id,$request);
        if(isset($data['errors'])){
            return response()->json($data['errors'],400);
        }
        return response()->json($data,200);
       
    }

 
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

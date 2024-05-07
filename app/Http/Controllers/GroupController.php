<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Group\Upsert as UpsertRequest;
use App\Services\GroupService;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    protected $groupService;
    public function __construct(GroupService $groupService)
    {
        $this->groupService = $groupService;
    }

    public function index(Request $request)

    {
       
        $data = $this->groupService->collection($request->all());
        if (isset($data['errors'])) {
            return response()->json($data['errors'],400);
        }
        return response()->json($data,200);
    }

    public function store(UpsertRequest $request)
    {
        $data = $this->groupService->store($request->validated());
        if (isset($data['errors'])) {
            return response()->json($data['errors'], 400);
        }
        return response()->json($data, 200);
    }

    public function show(int $id)
    {
        $groupMembers = $this->groupService->resource($id);
        if (isset($groupMembers['errors'])) {
            return response()->json($groupMembers['errors'], 400);
        }
        return response()->json($groupMembers, 200);
    }
    public function update($id, UpsertRequest $request)
    {
        $data = $this->groupService->update($id, $request->validated());
        if(isset($data['errors'])){
            return response()->json($data['errors'], 400); 
        }
        return response()->json($data, 200);
    }

    public function destroy($id)
    {
        $data = $this->groupService->delete($id);
        if(isset($data['errors'])){
            return response()->json($data['errors'], 400);
        }
        return response()->json($data, 200);
    }
}
